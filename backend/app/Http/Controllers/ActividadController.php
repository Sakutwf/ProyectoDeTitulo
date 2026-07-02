<?php

namespace App\Http\Controllers;

use App\Models\Actividad;
use App\Models\Archivo;
use App\Models\BoletaViatico;
use App\Models\GaleriaActividad;
use App\Models\Voluntario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class ActividadController extends Controller
{
    private const RELATIONS = ['filial', 'creador', 'voluntarios.user', 'climas.archivo'];

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
            'archivo' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'titulo' => ['nullable', 'string', 'max:150'],
            'descripcion' => ['nullable', 'string'],
            'fecha' => ['nullable', 'date'],
            'subido_por' => ['nullable', 'integer', 'exists:users,id'],
        ]);

        Storage::disk('public')->makeDirectory('actividades/galeria');

        $file = $request->file('archivo');
        $path = $file->store('actividades/galeria', 'public');

        $archivo = Archivo::create([
            'entidad' => 'actividad',
            'entidad_id' => $actividad->id,
            'categoria' => 'galeria_actividad',
            'ruta' => $path,
            'nombre_original' => $file->getClientOriginalName(),
            'extension' => $file->getClientOriginalExtension(),
            'mime_type' => $file->getClientMimeType(),
            'tamano' => $file->getSize(),
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

    public function boletas($id, Request $request)
    {
        $actividad = Actividad::findOrFail($id);

        $request->validate([
            'voluntario_id' => ['nullable', 'integer', 'exists:voluntarios,id'],
        ]);

        $query = $actividad->boletasViatico()->with(['archivo', 'voluntario.user', 'revisadoPor.voluntario']);

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

        Storage::disk('public')->makeDirectory('actividades/boletas');

        $file = $request->file('archivo');
        $path = $file->store('actividades/boletas', 'public');

        $archivo = Archivo::create([
            'entidad' => 'actividad',
            'entidad_id' => $actividad->id,
            'categoria' => 'boleta_viatico',
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
            'estado' => 'pendiente',
        ]);

        return response()->json(
            $boleta->load(['archivo', 'voluntario.user']),
            201
        );
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




