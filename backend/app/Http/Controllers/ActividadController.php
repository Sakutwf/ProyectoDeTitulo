<?php

namespace App\Http\Controllers;

use App\Models\Actividad;
use App\Models\ActividadClima;
use App\Models\Album;
use App\Models\Archivo;
use App\Models\BoletaViatico;
use App\Models\GaleriaActividad;
use App\Models\Voluntario;
use App\Services\ImageOptimizer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class ActividadController extends Controller
{
    public function __construct(private readonly ImageOptimizer $imageOptimizer) {}

    private const RELATIONS = ['filial', 'creador', 'voluntarios.user', 'climas.archivo'];

    private const BOLETA_ESTADO_SOLICITADO = 'solicitado';

    private const BOLETA_ESTADO_APROBADO = 'aprobado';

    private const BOLETA_ESTADO_PAGADO = 'pagado';

    private const BOLETA_ESTADOS = [
        self::BOLETA_ESTADO_SOLICITADO,
        self::BOLETA_ESTADO_APROBADO,
        self::BOLETA_ESTADO_PAGADO,
    ];

    public function index(Request $request)
    {
        $query = Actividad::with($this->activityRelations());

        if ($this->supportsDocumentSummary()) {
            $query->with(['documentos' => function ($documentQuery) {
                $documentQuery
                    ->select('id', 'actividad_id', 'tipo_documento', 'estado', 'updated_at')
                    ->whereIn('tipo_documento', ['analisis_contexto', 'informe_narrativo'])
                    ->whereIn('estado', ['borrador', 'final'])
                    ->latest('updated_at')
                    ->latest('id');
            }]);
        }

        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function ($subQuery) use ($search) {
                $subQuery->where('tipo', 'like', "%{$search}%")
                    ->orWhere('nombre', 'like', "%{$search}%")
                    ->orWhere('lugar', 'like', "%{$search}%")
                    ->orWhere('colaborador_externo', 'like', "%{$search}%");
            });
        }

        return response()->json($query->orderBy('id')->paginate(8), 200);
    }

    public function create()
    {
        //
    }

    public function store(Request $request)
    {
        $data = $this->validateActividad($request);
        $this->ensureVolunteerHoursWithinActivityTotal(
            $data['horas_totales'] ?? null,
            $request->input('voluntarios_detalle', [])
        );

        $actividad = Actividad::create($data);
        $this->syncVoluntarios($actividad, $request);
        $this->syncClimas($actividad, $request);

        return response()->json($actividad->load($this->activityRelations()), 201);
    }

    public function show($id)
    {
        return response()->json(Actividad::with($this->activityRelations())->findOrFail($id), 200);
    }

    public function edit(Actividad $actividad)
    {
        //
    }

    public function update($id, Request $request)
    {
        $actividad = Actividad::findOrFail($id);
        $data = $this->validateActividad($request, true);
        $this->ensureVolunteerHoursWithinActivityTotal(
            $data['horas_totales'] ?? $actividad->horas_totales,
            $request->input('voluntarios_detalle', [])
        );

        $actividad->update($data);
        $this->syncVoluntarios($actividad, $request);
        $this->syncClimas($actividad, $request);

        return response()->json($actividad->load($this->activityRelations()), 200);
    }

    public function destroy($id)
    {
        $actividad = Actividad::findOrFail($id);
        $actividad->delete();

        return response()->json(true, 200);
    }

    public function asociarVoluntario($id, Request $request)
    {
        $actividad = Actividad::findOrFail($id);

        $data = $request->validate([
            'voluntario_id' => ['required', 'integer', 'exists:voluntarios,id'],
            'horas_asistidas' => ['nullable', 'numeric', 'min:0'],
            'registrado_por' => ['nullable', 'integer', 'exists:users,id'],
        ]);

        $this->ensureVolunteerHoursWithinActivityTotal($actividad->horas_totales, [[
            'voluntario_id' => $data['voluntario_id'],
            'horas_asistidas' => $data['horas_asistidas'] ?? 0,
        ]]);

        $actividad->voluntarios()->syncWithoutDetaching([
            $data['voluntario_id'] => [
                'horas_asistidas' => $data['horas_asistidas'] ?? 0,
                'registrado_por' => $data['registrado_por'] ?? null,
            ],
        ]);

        return response()->json($actividad->load($this->activityRelations()), 200);
    }

    public function desasociarVoluntario($id, Request $request)
    {
        $actividad = Actividad::findOrFail($id);
        $data = $request->validate([
            'voluntario_id' => ['required', 'integer', 'exists:voluntarios,id'],
        ]);

        $actividad->voluntarios()->detach($data['voluntario_id']);

        return response()->json($actividad->load($this->activityRelations()), 200);
    }

    public function guardarClimas($id, Request $request)
    {
        $actividad = Actividad::findOrFail($id);
        $this->validateClimasPayload($request);
        $this->syncClimas($actividad, $request);

        $freshActividad = $actividad->fresh();

        if (! $this->supportsClimateRelations()) {
            return response()->json($freshActividad, 200);
        }

        return response()->json(
            $freshActividad->load(['climas.archivo']),
            200
        );
    }

    public function galeria($id)
    {
        $actividad = Actividad::with([
            'galeria.archivo',
            'galeria.subidoPor.voluntario',
            'albumes.fotos.subidoPor.voluntario',
        ])->findOrFail($id);

        $galeria = $actividad->galeria
            ->filter(fn ($item) => $item->archivo?->categoria !== 'clima_documento')
            ->map(fn ($item) => [
                'id' => $item->id,
                'archivo_id' => $item->archivo_id,
                'titulo' => $item->titulo,
                'descripcion' => $item->descripcion,
                'fecha' => optional($item->fecha)->format('Y-m-d'),
                'imagen_url' => $item->imagen_url,
                'archivo' => $item->archivo,
                'subido_por' => $item->subidoPor,
                'origen' => 'galeria_actividad',
            ]);

        $albumPhotos = $actividad->albumes
            ->flatMap(fn ($album) => $album->fotos->map(fn ($foto) => [
                'id' => 'album-'.$foto->id,
                'archivo_id' => $foto->id,
                'titulo' => $foto->nombre_original ?: $album->nombre,
                'descripcion' => $foto->descripcion ?: $album->descripcion,
                'fecha' => optional($foto->created_at)->format('Y-m-d'),
                'imagen_url' => $foto->url_publica,
                'archivo' => $foto,
                'subido_por' => $foto->subidoPor,
                'origen' => 'album',
                'album_id' => $album->id,
                'album_nombre' => $album->nombre,
            ]));

        return response()->json(
            $galeria->concat($albumPhotos)
                ->filter(fn ($item) => ! empty($item['imagen_url']))
                ->unique('imagen_url')
                ->sortByDesc(fn ($item) => $item['fecha'] ?? '')
                ->values(),
            200
        );
    }

    public function subirImagenGaleria($id, Request $request)
    {
        $actividad = Actividad::findOrFail($id);

        $data = $request->validate([
            'archivo' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:10240'],
            'titulo' => ['nullable', 'string', 'max:150'],
            'descripcion' => ['nullable', 'string'],
            'fecha' => ['nullable', 'date'],
            'subido_por' => ['nullable', 'integer', 'exists:users,id'],
            'categoria' => ['nullable', 'string', 'in:galeria_actividad,clima_documento'],
        ]);

        Storage::disk('public')->makeDirectory('actividades/galeria');

        $file = $request->file('archivo');
        $optimized = $this->imageOptimizer->store($file, 'actividades/galeria');

        $archivo = Archivo::create([
            'entidad' => 'actividad',
            'entidad_id' => $actividad->id,
            'categoria' => $data['categoria'] ?? 'galeria_actividad',
            'ruta' => $optimized['path'],
            'nombre_original' => $optimized['original_name'],
            'extension' => $optimized['extension'],
            'mime_type' => $optimized['mime_type'],
            'tamano' => $optimized['size'],
            'descripcion' => $data['descripcion'] ?? null,
            'subido_por' => $data['subido_por'] ?? $request->user()?->id,
        ]);

        $registro = $actividad->galeria()->create([
            'archivo_id' => $archivo->id,
            'titulo' => $data['titulo'] ?? null,
            'descripcion' => $data['descripcion'] ?? null,
            'fecha' => $data['fecha'] ?? now()->toDateString(),
            'subido_por' => $data['subido_por'] ?? $request->user()?->id,
        ]);

        return response()->json(
            $registro->load(['archivo', 'subidoPor.voluntario']),
            201
        );
    }

    public function eliminarImagenTemporalClima($id, Archivo $archivo)
    {
        Actividad::findOrFail($id);

        abort_unless(
            $archivo->entidad === 'actividad'
            && (int) $archivo->entidad_id === (int) $id
            && $archivo->categoria === 'clima_documento',
            404
        );

        DB::transaction(function () use ($id, $archivo) {
            ActividadClima::query()
                ->where('actividad_id', $id)
                ->where('archivo_id', $archivo->id)
                ->update(['archivo_id' => null]);

            GaleriaActividad::query()->where('archivo_id', $archivo->id)->delete();

            if ($archivo->ruta) {
                Storage::disk('public')->delete($archivo->ruta);
            }

            $archivo->delete();
        });

        return response()->json(null, 204);
    }

    public function boletas($id, Request $request)
    {
        $actividad = Actividad::findOrFail($id);

        $request->validate([
            'voluntario_id' => ['nullable', 'integer', 'exists:voluntarios,id'],
        ]);

        $query = $actividad->boletasViatico()->with(['archivo', 'voluntario.user', 'revisadoPor.voluntario', 'actividad:id,nombre']);

        if ($request->filled('voluntario_id')) {
            $query->where('voluntario_id', $request->integer('voluntario_id'));
        }

        return response()->json(
            $query->latest('fecha_compra')
                ->latest('id')
                ->get(),
            200
        );
    }

    public function gestionBoletas(Request $request)
    {
        $actor = $request->user()?->loadMissing('roles', 'voluntario.filial');

        abort_unless($this->canReviewBoletas($actor), 403, 'No tienes permisos para revisar boletas.');

        $estado = null;

        if ($request->filled('estado')) {
            $estado = $this->normalizeBoletaEstado($request->input('estado'));
        }

        $query = BoletaViatico::query()
            ->with(['archivo', 'voluntario.user', 'voluntario.filial', 'revisadoPor.voluntario', 'actividad:id,nombre']);

        $this->scopeBoletasToActorFilial($query, $actor);

        if ($estado) {
            $query->where('estado', $estado);
        }

        $query->orderByRaw($this->boletaPriorityOrderSql());

        if ($this->boletasHasFechaPagoColumn()) {
            $query->latest('fecha_pago');
        }

        return response()->json(
            $query->latest('fecha_compra')
                ->latest('id')
                ->get(),
            200
        );
    }

    public function actualizarEstadoBoleta(Request $request, BoletaViatico $boletaViatico)
    {
        $actor = $request->user()?->loadMissing('roles', 'voluntario.filial');

        abort_unless($this->canReviewBoletas($actor), 403, 'No tienes permisos para actualizar el estado de esta boleta.');
        abort_unless($this->canReviewBoletaFromActorFilial($actor, $boletaViatico), 403, 'No tienes permisos para actualizar boletas de otra filial.');

        $data = $request->validate([
            'estado' => ['required', 'string', 'max:50'],
            'fecha_pago' => ['nullable', 'date'],
        ]);

        $estado = $this->normalizeBoletaEstado($data['estado']);

        if (! in_array($estado, self::BOLETA_ESTADOS, true)) {
            throw ValidationException::withMessages([
                'estado' => ['El estado de la boleta no es valido.'],
            ]);
        }

        $boletaViatico->estado = $estado;
        $boletaViatico->revisado_por = $actor?->id;

        if ($this->boletasHasFechaPagoColumn()) {
            $boletaViatico->fecha_pago = $estado === self::BOLETA_ESTADO_PAGADO
                ? ($data['fecha_pago'] ?? now()->toDateString())
                : null;
        }

        $boletaViatico->save();

        return response()->json(
            $boletaViatico->fresh()->load(['archivo', 'voluntario.user', 'voluntario.filial', 'revisadoPor.voluntario', 'actividad:id,nombre']),
            200
        );
    }

    public function subirBoleta($id, Request $request)
    {
        $actividad = Actividad::findOrFail($id);

        $data = $request->validate([
            'voluntario_id' => ['required', 'integer', 'exists:voluntarios,id'],
            'archivo' => ['required', 'file', 'mimes:jpg,jpeg,png,webp,pdf', 'max:5120'],
            'detalle_compra' => ['required', 'string', 'max:255'],
            'monto' => ['required', 'numeric', 'min:0'],
            'fecha_compra' => ['nullable', 'date'],
        ]);

        $voluntario = Voluntario::findOrFail($data['voluntario_id']);

        $isLinkedToActivity = $actividad->voluntarios()
            ->where('voluntarios.id', $voluntario->id)
            ->exists();

        if (! $isLinkedToActivity) {
            return response()->json([
                'message' => 'El voluntario no pertenece a la actividad seleccionada.',
            ], 422);
        }

        $album = $this->ensureReceiptAlbum($actividad, $request->user()?->id ?? $voluntario->user_id);

        Storage::disk('public')->makeDirectory('albumes/'.$album->id.'/boletas');

        $file = $request->file('archivo');
        $path = $file->store('albumes/'.$album->id.'/boletas', 'public');

        $archivo = Archivo::create([
            'entidad' => 'album',
            'entidad_id' => $album->id,
            'categoria' => 'boleta_album',
            'ruta' => $path,
            'nombre_original' => $file->getClientOriginalName(),
            'extension' => $file->getClientOriginalExtension(),
            'mime_type' => $file->getClientMimeType(),
            'tamano' => $file->getSize(),
            'descripcion' => $data['detalle_compra'],
            'subido_por' => $request->user()?->id ?? $voluntario->user_id,
        ]);

        $boleta = $actividad->boletasViatico()->create([
            'voluntario_id' => $voluntario->id,
            'archivo_id' => $archivo->id,
            'detalle_compra' => $data['detalle_compra'],
            'monto' => $data['monto'],
            'fecha_compra' => $data['fecha_compra'] ?? now()->toDateString(),
            'estado' => self::BOLETA_ESTADO_SOLICITADO,
        ]);

        return response()->json(
            $boleta->load(['archivo', 'voluntario.user', 'actividad:id,nombre']),
            201
        );
    }

    public function actualizarBoleta(Request $request, BoletaViatico $boletaViatico)
    {
        $actor = $request->user()?->loadMissing('roles', 'voluntario');

        abort_unless($this->canManageBoleta($actor, $boletaViatico), 403, 'No tienes permisos para actualizar esta boleta.');

        $data = $request->validate([
            'actividad_id' => ['required', 'integer', 'exists:actividades,id'],
            'archivo' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp,pdf', 'max:5120'],
            'detalle_compra' => ['required', 'string', 'max:255'],
            'monto' => ['required', 'numeric', 'min:0'],
            'fecha_compra' => ['nullable', 'date'],
        ]);

        $actividad = Actividad::findOrFail($data['actividad_id']);
        $this->ensureVolunteerBelongsToActivity($actividad, (int) $boletaViatico->voluntario_id);

        $album = $this->ensureReceiptAlbum($actividad, $actor?->id ?? $boletaViatico->voluntario?->user_id);

        if ($request->hasFile('archivo')) {
            $archivo = $this->replaceBoletaArchivo($request, $boletaViatico, $album, $data['detalle_compra']);
            $boletaViatico->archivo_id = $archivo->id;
        } elseif ($boletaViatico->archivo) {
            $boletaViatico->archivo->update([
                'entidad_id' => $album->id,
                'descripcion' => $data['detalle_compra'],
            ]);
        }

        $boletaViatico->actividad_id = $actividad->id;
        $boletaViatico->detalle_compra = $data['detalle_compra'];
        $boletaViatico->monto = $data['monto'];
        $boletaViatico->fecha_compra = $data['fecha_compra'] ?? $boletaViatico->fecha_compra ?? now()->toDateString();
        $boletaViatico->save();

        return response()->json(
            $boletaViatico->fresh()->load(['archivo', 'voluntario.user', 'revisadoPor.voluntario', 'actividad:id,nombre']),
            200
        );
    }

    public function eliminarBoleta(Request $request, BoletaViatico $boletaViatico)
    {
        $actor = $request->user()?->loadMissing('roles', 'voluntario');

        abort_unless($this->canManageBoleta($actor, $boletaViatico), 403, 'No tienes permisos para eliminar esta boleta.');

        $this->deleteBoletaArchivo($boletaViatico->archivo);
        $boletaViatico->delete();

        return response()->json([
            'message' => 'Boleta eliminada correctamente.',
        ], 200);
    }

    private function ensureReceiptAlbum(Actividad $actividad, ?int $creatorId): Album
    {
        return Album::query()->firstOrCreate(
            [
                'actividad_id' => $actividad->id,
                'nombre' => 'Boletas de viatico',
            ],
            [
                'descripcion' => 'Album generado automaticamente para respaldos de boletas y viaticos de la actividad.',
                'creado_por' => $creatorId,
            ]
        );
    }

    private function ensureVolunteerBelongsToActivity(Actividad $actividad, int $voluntarioId): void
    {
        $isLinkedToActivity = $actividad->voluntarios()
            ->where('voluntarios.id', $voluntarioId)
            ->exists();

        if (! $isLinkedToActivity) {
            throw ValidationException::withMessages([
                'actividad_id' => ['El voluntario no pertenece a la actividad seleccionada.'],
            ]);
        }
    }

    private function canManageBoleta($actor, BoletaViatico $boletaViatico): bool
    {
        if (! $actor) {
            return false;
        }

        $actor->loadMissing('roles', 'voluntario.filial');
        $boletaViatico->loadMissing('voluntario');

        if ($this->canReviewBoletas($actor)) {
            return $this->canReviewBoletaFromActorFilial($actor, $boletaViatico);
        }

        return (int) ($actor->voluntario?->id ?? 0) === (int) $boletaViatico->voluntario_id
            || (int) ($actor->id ?? 0) === (int) ($boletaViatico->voluntario?->user_id ?? 0);
    }

    private function canReviewBoletas($actor): bool
    {
        if (! $actor) {
            return false;
        }

        $actor->loadMissing('roles');

        return $actor->roles->contains(fn ($role) => in_array($role->clave, ['administrador', 'secretario-directiva'], true));
    }

    private function canReviewBoletaFromActorFilial($actor, BoletaViatico $boletaViatico): bool
    {
        if (! $actor) {
            return false;
        }

        $actor->loadMissing('voluntario');
        $boletaViatico->loadMissing('voluntario');

        $actorFilialId = (int) ($actor->voluntario?->filial_id ?? 0);

        if ($actorFilialId <= 0) {
            return true;
        }

        return (int) ($boletaViatico->voluntario?->filial_id ?? 0) === $actorFilialId;
    }

    private function scopeBoletasToActorFilial($query, $actor): void
    {
        $actorFilialId = (int) ($actor?->voluntario?->filial_id ?? 0);

        if ($actorFilialId <= 0) {
            return;
        }

        $query->whereHas('voluntario', function ($volunteerQuery) use ($actorFilialId) {
            $volunteerQuery->where('filial_id', $actorFilialId);
        });
    }

    private function normalizeBoletaEstado($estado): string
    {
        $value = strtolower(trim((string) $estado));

        return match ($value) {
            'pendiente', 'solicitada', 'solicitado', '' => self::BOLETA_ESTADO_SOLICITADO,
            'aprobada', 'aprobado' => self::BOLETA_ESTADO_APROBADO,
            'pagada', 'pagado' => self::BOLETA_ESTADO_PAGADO,
            default => $value,
        };
    }

    private function boletaPriorityOrderSql(): string
    {
        return "CASE LOWER(COALESCE(estado, '')) WHEN 'solicitado' THEN 0 WHEN 'aprobado' THEN 1 WHEN 'pagado' THEN 2 ELSE 3 END";
    }

    private function boletasHasFechaPagoColumn(): bool
    {
        return Schema::hasColumn('boletas_viatico', 'fecha_pago');
    }

    private function replaceBoletaArchivo(Request $request, BoletaViatico $boletaViatico, Album $album, string $descripcion): Archivo
    {
        $this->deleteBoletaArchivo($boletaViatico->archivo);

        Storage::disk('public')->makeDirectory('albumes/'.$album->id.'/boletas');

        $file = $request->file('archivo');
        $path = $file->store('albumes/'.$album->id.'/boletas', 'public');

        return Archivo::create([
            'entidad' => 'album',
            'entidad_id' => $album->id,
            'categoria' => 'boleta_album',
            'ruta' => $path,
            'nombre_original' => $file->getClientOriginalName(),
            'extension' => $file->getClientOriginalExtension(),
            'mime_type' => $file->getClientMimeType(),
            'tamano' => $file->getSize(),
            'descripcion' => $descripcion,
            'subido_por' => $request->user()?->id ?? $boletaViatico->voluntario?->user_id,
        ]);
    }

    private function deleteBoletaArchivo(?Archivo $archivo): void
    {
        if (! $archivo) {
            return;
        }

        if ($archivo->ruta) {
            Storage::disk('public')->delete($archivo->ruta);
        }

        $archivo->delete();
    }

    private function normalizeActividadAuditReferences(Request $request): void
    {
        $currentUserId = $request->user()?->id;

        $creadoPor = $request->input('creado_por');

        if ($creadoPor === '' || $creadoPor === null) {
            if ($currentUserId !== null) {
                $request->merge(['creado_por' => $currentUserId]);
            } else {
                $request->request->remove('creado_por');
            }
        }

        if (! $request->has('voluntarios_detalle') || ! is_array($request->input('voluntarios_detalle'))) {
            return;
        }

        $normalizedDetails = collect($request->input('voluntarios_detalle', []))
            ->map(function ($detalle) use ($currentUserId) {
                if (! is_array($detalle)) {
                    return $detalle;
                }

                $registradoPor = $detalle['registrado_por'] ?? null;

                if ($registradoPor === '' || $registradoPor === null) {
                    if ($currentUserId !== null) {
                        $detalle['registrado_por'] = $currentUserId;
                    } else {
                        unset($detalle['registrado_por']);
                    }
                }

                return $detalle;
            })
            ->all();

        $request->merge(['voluntarios_detalle' => $normalizedDetails]);
    }

    private function normalizeTimeField(Request $request, string $field): void
    {
        $value = $request->input($field);

        if (! is_string($value)) {
            return;
        }

        $trimmedValue = trim($value);

        if ($trimmedValue === '') {
            return;
        }

        try {
            $normalizedTime = new \DateTimeImmutable($trimmedValue);
        } catch (\Exception $exception) {
            return;
        }

        $request->merge([$field => $normalizedTime->format('H:i')]);
    }

    private function normalizeHorasTotalesFromSchedule(Request $request): void
    {
        $rawHours = $request->input('horas_totales');

        if (! ($rawHours === '' || $rawHours === null)) {
            return;
        }

        $start = $request->input('hora_inicio');
        $end = $request->input('hora_termino');

        if (! is_string($start) || ! is_string($end) || trim($start) === '' || trim($end) === '') {
            return;
        }

        try {
            $startTime = new \DateTimeImmutable(trim($start));
            $endTime = new \DateTimeImmutable(trim($end));
        } catch (\Exception $exception) {
            return;
        }

        if ($endTime <= $startTime) {
            return;
        }

        $minutes = ($endTime->getTimestamp() - $startTime->getTimestamp()) / 60;
        $hours = round($minutes / 60, 2);

        if ($hours > 0) {
            $request->merge(['horas_totales' => $hours]);
        }
    }

    private function activityRelations(): array
    {
        if (! $this->supportsClimateRelations()) {
            return array_values(array_filter(
                self::RELATIONS,
                fn (string $relation) => $relation !== 'climas.archivo'
            ));
        }

        return self::RELATIONS;
    }

    private function supportsClimateRelations(): bool
    {
        return Schema::hasTable('actividad_climas')
            && Schema::hasColumns('actividad_climas', ['actividad_id', 'archivo_id']);
    }

    private function supportsDocumentSummary(): bool
    {
        return Schema::hasTable('documentos_actividad')
            && Schema::hasColumns('documentos_actividad', [
                'actividad_id',
                'tipo_documento',
                'estado',
                'updated_at',
            ]);
    }

    private function validateActividad(Request $request, bool $partial = false): array
    {
        $required = $partial ? 'sometimes' : 'required';
        $this->normalizeActividadAuditReferences($request);
        $this->normalizeTimeField($request, 'hora_inicio');
        $this->normalizeTimeField($request, 'hora_termino');
        $this->normalizeHorasTotalesFromSchedule($request);

        return $request->validate([
            'filial_id' => [$required, 'integer', 'exists:filiales,id'],
            'creado_por' => ['nullable', 'integer', 'exists:users,id'],
            'nombre' => [$required, 'string', 'max:200'],
            'tipo' => [$required, 'string', 'max:100'],
            'objetivo' => ['nullable', 'string'],
            'fecha_inicio' => [$required, 'date'],
            'fecha_termino' => ['nullable', 'date', 'after_or_equal:fecha_inicio'],
            'hora_inicio' => ['nullable', 'date_format:H:i'],
            'hora_termino' => ['nullable', 'date_format:H:i'],
            'lugar' => ['nullable', 'string', 'max:255'],
            'horas_totales' => ['nullable', 'numeric', 'min:0'],
            'colaborador_externo' => ['nullable', 'string', 'max:200'],
            'climas' => ['sometimes', 'array'],
            'climas.*.id' => ['nullable', 'integer'],
            'climas.*.temperatura_minima' => ['nullable', 'numeric', 'between:-99.99,99.99'],
            'climas.*.temperatura_maxima' => ['nullable', 'numeric', 'between:-99.99,99.99'],
            'climas.*.tipo_clima' => ['nullable', 'string', 'max:150'],
            'climas.*.archivo_id' => ['nullable', 'integer', 'exists:archivos,id'],
            'voluntarios' => ['sometimes', 'array'],
            'voluntarios.*' => ['integer', 'exists:voluntarios,id'],
            'voluntarios_detalle' => ['sometimes', 'array'],
            'voluntarios_detalle.*.voluntario_id' => ['required_with:voluntarios_detalle', 'integer', 'exists:voluntarios,id'],
            'voluntarios_detalle.*.horas_asistidas' => ['nullable', 'numeric', 'min:0'],
            'voluntarios_detalle.*.registrado_por' => ['nullable', 'integer', 'exists:users,id'],
        ]);
    }

    private function validateClimasPayload(Request $request): array
    {
        return $request->validate([
            'climas' => ['nullable', 'array'],
            'climas.*.id' => ['nullable', 'integer'],
            'climas.*.temperatura_minima' => ['nullable', 'numeric', 'between:-99.99,99.99'],
            'climas.*.temperatura_maxima' => ['nullable', 'numeric', 'between:-99.99,99.99'],
            'climas.*.tipo_clima' => ['nullable', 'string', 'max:150'],
            'climas.*.archivo_id' => ['nullable', 'integer', 'exists:archivos,id'],
        ]);
    }

    private function syncVoluntarios(Actividad $actividad, Request $request): void
    {
        if ($request->has('voluntarios_detalle')) {
            $syncData = collect($request->input('voluntarios_detalle', []))
                ->mapWithKeys(fn (array $detalle) => [
                    $detalle['voluntario_id'] => [
                        'horas_asistidas' => $detalle['horas_asistidas'] ?? 0,
                        'registrado_por' => $detalle['registrado_por'] ?? null,
                    ],
                ])
                ->all();

            $actividad->voluntarios()->sync($syncData);

            return;
        }

        if ($request->has('voluntarios')) {
            $syncData = collect($request->input('voluntarios', []))
                ->mapWithKeys(fn (int $voluntarioId) => [
                    $voluntarioId => ['horas_asistidas' => 0, 'registrado_por' => null],
                ])
                ->all();

            $actividad->voluntarios()->sync($syncData);
        }
    }

    private function syncClimas(Actividad $actividad, Request $request): void
    {
        if (! $request->has('climas') || ! $this->supportsClimateRelations()) {
            return;
        }

        $rows = collect($request->input('climas', []))
            ->filter(function ($row) {
                if (! is_array($row)) {
                    return false;
                }

                return collect([
                    $row['temperatura_minima'] ?? null,
                    $row['temperatura_maxima'] ?? null,
                    $row['tipo_clima'] ?? null,
                    $row['archivo_id'] ?? null,
                ])->contains(fn ($value) => $value !== null && $value !== '');
            })
            ->values()
            ->map(fn (array $row, int $index) => [
                'id' => isset($row['id']) ? (int) $row['id'] : null,
                'orden' => $index + 1,
                'temperatura_minima' => $row['temperatura_minima'] ?? null,
                'temperatura_maxima' => $row['temperatura_maxima'] ?? null,
                'tipo_clima' => isset($row['tipo_clima']) ? trim((string) $row['tipo_clima']) : null,
                'archivo_id' => $row['archivo_id'] ?? null,
            ]);

        $this->ensureClimateFilesBelongToActivity($actividad, $rows->all());

        $existingIds = $actividad->climas()->pluck('id')->map(fn ($id) => (int) $id)->all();
        $keptIds = $rows->pluck('id')->filter()->map(fn ($id) => (int) $id)->all();
        $idsToDelete = array_values(array_diff($existingIds, $keptIds));

        if ($idsToDelete !== []) {
            $actividad->climas()->whereIn('id', $idsToDelete)->delete();
        }

        if ($rows->isEmpty()) {
            $actividad->climas()->delete();

            return;
        }

        foreach ($rows as $row) {
            $payload = [
                'orden' => $row['orden'],
                'temperatura_minima' => $row['temperatura_minima'],
                'temperatura_maxima' => $row['temperatura_maxima'],
                'tipo_clima' => $row['tipo_clima'],
                'archivo_id' => $row['archivo_id'],
            ];

            if ($row['id']) {
                $actividad->climas()
                    ->whereKey($row['id'])
                    ->update($payload);

                continue;
            }

            $actividad->climas()->create($payload);
        }
    }

    private function ensureVolunteerHoursWithinActivityTotal($horasTotales, array $voluntariosDetalle): void
    {
        $activityHours = $horasTotales === null || $horasTotales === '' ? null : (float) $horasTotales;
        $details = collect($voluntariosDetalle)
            ->values()
            ->map(fn (array $detalle) => [
                'voluntario_id' => $detalle['voluntario_id'] ?? null,
                'horas_asistidas' => (float) ($detalle['horas_asistidas'] ?? 0),
            ])
            ->all();

        $hasAssignedHours = collect($details)->contains(fn (array $detalle) => $detalle['horas_asistidas'] > 0);

        if (! $hasAssignedHours) {
            return;
        }

        if ($activityHours === null) {
            throw ValidationException::withMessages([
                'horas_totales' => ['Debes ingresar las horas totales de la actividad antes de asignar horas a voluntarios.'],
            ]);
        }

        $errors = [];

        foreach ($details as $index => $detalle) {
            if ($detalle['horas_asistidas'] > $activityHours) {
                $errors["voluntarios_detalle.$index.horas_asistidas"] = [
                    'Las horas asignadas a cada voluntario no pueden superar las horas totales de la actividad.',
                ];
            }
        }

        if ($errors !== []) {
            throw ValidationException::withMessages($errors);
        }
    }

    private function ensureClimateFilesBelongToActivity(Actividad $actividad, array $rows): void
    {
        $fileIds = collect($rows)
            ->pluck('archivo_id')
            ->filter()
            ->map(fn ($id) => (int) $id)
            ->values();

        if ($fileIds->isEmpty()) {
            return;
        }

        $validFileIds = GaleriaActividad::query()
            ->where('actividad_id', $actividad->id)
            ->whereIn('archivo_id', $fileIds->all())
            ->pluck('archivo_id')
            ->map(fn ($id) => (int) $id)
            ->all();

        foreach ($rows as $index => $row) {
            $fileId = isset($row['archivo_id']) ? (int) $row['archivo_id'] : null;

            if ($fileId && ! in_array($fileId, $validFileIds, true)) {
                throw ValidationException::withMessages([
                    "climas.$index.archivo_id" => ['La imagen seleccionada no pertenece a la galeria de esta actividad.'],
                ]);
            }
        }
    }
}
