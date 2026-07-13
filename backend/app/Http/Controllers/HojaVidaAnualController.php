<?php

namespace App\Http\Controllers;

use App\Models\Archivo;
use App\Models\CursoVoluntario;
use App\Models\HojaVidaAnual;
use App\Models\TituloVoluntario;
use App\Models\User;
use App\Models\Voluntario;
use App\Services\ImageOptimizer;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class HojaVidaAnualController extends Controller
{
    public function __construct(private readonly ImageOptimizer $imageOptimizer) {}

    private const VOLUNTEER_CARGO_CATALOG = [
        'gobernanza_presidente' => ['tipo' => 'Gobernanza', 'nombre' => 'Presidente', 'direccion' => null],
        'gobernanza_vicepresidente' => ['tipo' => 'Gobernanza', 'nombre' => 'Vicepresidente', 'direccion' => null],
        'gobernanza_secretario' => ['tipo' => 'Gobernanza', 'nombre' => 'Secretario', 'direccion' => null],
        'gobernanza_finanzas' => ['tipo' => 'Gobernanza', 'nombre' => 'Finanzas', 'direccion' => null],
        'directorio_director_salud' => ['tipo' => 'Directorio', 'nombre' => 'Director', 'direccion' => 'Salud'],
        'directorio_director_subrogante_salud' => ['tipo' => 'Directorio', 'nombre' => 'Director Subrogante', 'direccion' => 'Salud'],
        'directorio_director_juventud' => ['tipo' => 'Directorio', 'nombre' => 'Director', 'direccion' => 'Juventud'],
        'directorio_director_subrogante_juventud' => ['tipo' => 'Directorio', 'nombre' => 'Director Subrogante', 'direccion' => 'Juventud'],
        'directorio_director_gestion' => ['tipo' => 'Directorio', 'nombre' => 'Director', 'direccion' => 'Gestion'],
        'directorio_director_subrogante_gestion' => ['tipo' => 'Directorio', 'nombre' => 'Director Subrogante', 'direccion' => 'Gestion'],
        'directorio_director_desarrollo' => ['tipo' => 'Directorio', 'nombre' => 'Director', 'direccion' => 'Desarrollo'],
        'directorio_director_subrogante_desarrollo' => ['tipo' => 'Directorio', 'nombre' => 'Director Subrogante', 'direccion' => 'Desarrollo'],
        'directorio_director_bienestar_social' => ['tipo' => 'Directorio', 'nombre' => 'Director', 'direccion' => 'Bienestar Social'],
        'directorio_director_subrogante_bienestar_social' => ['tipo' => 'Directorio', 'nombre' => 'Director Subrogante', 'direccion' => 'Bienestar Social'],
        'directorio_director_comunicaciones' => ['tipo' => 'Directorio', 'nombre' => 'Director', 'direccion' => 'Comunicaciones'],
        'directorio_director_subrogante_comunicaciones' => ['tipo' => 'Directorio', 'nombre' => 'Director Subrogante', 'direccion' => 'Comunicaciones'],
    ];

    private const RELATIONS = [
        'voluntario.filial',
        'voluntario.archivoFotoPerfil',
        'voluntario.user.roles.permissions',
        'titulos.archivo',
        'titulos.archivosAdjuntos',
        'cursos.archivo',
        'cursos.archivosAdjuntos',
        'otrosDocumentos.archivo',
        'otrosDocumentos.archivosAdjuntos',
        'sanciones',
        'reconocimiento',
        'generador',
    ];

    public function index(Voluntario $voluntario)
    {
        return response()->json(
            $voluntario->hojaVidaAnual()
                ->with(['titulos.archivo', 'titulos.archivosAdjuntos', 'cursos.archivo', 'cursos.archivosAdjuntos', 'otrosDocumentos.archivo', 'otrosDocumentos.archivosAdjuntos', 'sanciones', 'reconocimiento', 'generador'])
                ->orderByDesc('anio')
                ->get(),
            200
        );
    }

    public function store(Request $request, Voluntario $voluntario)
    {
        if ($this->isVolunteerSelfServiceRequest($request, $voluntario->id)) {
            return response()->json([
                'message' => 'No puedes crear una hoja de vida anual desde tu perfil.',
            ], 403);
        }

        $validated = $this->validateRequest($request, $voluntario->id);

        $hojaVidaAnual = DB::transaction(function () use ($validated, $voluntario, $request) {
            $record = HojaVidaAnual::create(
                $this->buildMainPayload($validated, $voluntario->id)
            );

            $this->syncRelations($record, $validated, $request);
            $this->syncVolunteerProfile($voluntario, $validated, $request);

            return $record->fresh()->load(self::RELATIONS);
        });

        return response()->json($hojaVidaAnual, 201);
    }

    public function show(HojaVidaAnual $hojaVidaAnual)
    {
        return response()->json(
            $hojaVidaAnual->load(self::RELATIONS),
            200
        );
    }

    public function update(Request $request, HojaVidaAnual $hojaVidaAnual)
    {
        $isVolunteerSelfService = $this->isVolunteerSelfServiceRequest($request, $hojaVidaAnual->voluntario_id);

        if ($isVolunteerSelfService) {
            $this->limitVolunteerEditableSections($request, $hojaVidaAnual);
        }

        $validated = $this->validateRequest($request, $hojaVidaAnual->voluntario_id, $hojaVidaAnual);

        $hojaVidaAnual = DB::transaction(function () use ($validated, $hojaVidaAnual, $request, $isVolunteerSelfService) {
            $hojaVidaAnual->update(
                $this->buildMainPayload($validated, $hojaVidaAnual->voluntario_id, $hojaVidaAnual)
            );

            $this->syncRelations($hojaVidaAnual, $validated, $request, $isVolunteerSelfService);
            $this->syncVolunteerProfile($hojaVidaAnual->voluntario, $validated, $request);

            return $hojaVidaAnual->fresh()->load(self::RELATIONS);
        });

        return response()->json($hojaVidaAnual, 200);
    }

    public function destroy(HojaVidaAnual $hojaVidaAnual)
    {
        $hojaVidaAnual->delete();

        return response()->json(null, 204);
    }

    private function validateRequest(Request $request, int $voluntarioId, ?HojaVidaAnual $hojaVidaAnual = null): array
    {
        $request->merge([
            'cargo_clave' => $this->normalizeText($request->input('cargo_clave')),
            'generada_por' => $this->normalizeOptionalUserId($request->input('generada_por')),
        ]);

        $validated = $request->validate([
            'anio' => [
                'required',
                'integer',
                'between:1900,2100',
                Rule::unique('hoja_vida_anual', 'anio')
                    ->where(fn ($query) => $query->where('voluntario_id', $voluntarioId))
                    ->ignore($hojaVidaAnual?->id),
            ],
            'asistencia_anual_horas' => ['nullable', 'numeric', 'min:0'],
            'asistencia_anual_porcentaje' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'asistencia_anual_ajuste_horas' => ['nullable', 'numeric', 'between:-9999.99,9999.99'],
            'asistencia_reuniones_filial_ajuste_horas' => ['nullable', 'numeric', 'between:-9999.99,9999.99'],
            'asistencia_actividades_voluntariado_ajuste_horas' => ['nullable', 'numeric', 'between:-9999.99,9999.99'],
            'asistencia_horas_filial_ajuste_horas' => ['nullable', 'numeric', 'between:-9999.99,9999.99'],
            'asistencia_horas_formativas_ajuste_horas' => ['nullable', 'numeric', 'between:-9999.99,9999.99'],
            'estuvo_comision_servicio' => ['nullable', 'boolean'],
            'comision_fecha_inicio' => ['nullable', 'date'],
            'comision_fecha_termino' => ['nullable', 'date', 'after_or_equal:comision_fecha_inicio'],
            'comision_lugar' => ['nullable', 'string', 'max:255'],
            'comision_actividad' => ['nullable', 'string'],
            'comentarios' => ['nullable', 'string'],
            'cargo_clave' => ['nullable', 'string', Rule::in(array_keys(self::VOLUNTEER_CARGO_CATALOG))],
            'generada_por' => ['nullable', 'integer', 'exists:users,id'],
            'fecha_generacion' => ['nullable', 'date'],
            'registro_filial' => ['sometimes', 'required', 'string', 'max:50'],
            'filial_id' => ['sometimes', 'required', 'integer', 'exists:filiales,id'],
            'rut' => ['sometimes', 'required', 'string', 'max:20', Rule::unique('voluntarios', 'rut')->ignore($voluntarioId)],
            'nombres' => ['sometimes', 'required', 'string', 'max:150'],
            'apellidos' => ['sometimes', 'required', 'string', 'max:150'],
            'nacionalidad' => ['sometimes', 'nullable', 'string', 'max:100'],
            'fecha_nacimiento' => ['sometimes', 'nullable', 'date'],
            'fecha_incorporacion' => ['sometimes', 'nullable', 'date'],
            'nivel_escolaridad' => ['sometimes', 'nullable', 'string', 'max:100'],
            'estado_civil' => ['sometimes', 'nullable', 'string', 'max:100'],
            'ocupacion' => ['sometimes', 'nullable', 'string', 'max:150'],
            'grupo_sanguineo' => ['sometimes', 'nullable', 'string', 'max:20'],
            'correo_electronico' => ['sometimes', 'nullable', 'email', 'max:150'],
            'celular' => ['sometimes', 'nullable', 'string', 'max:30'],
            'domicilio' => ['sometimes', 'nullable', 'string', 'max:255'],
            'enfermedades' => ['sometimes', 'nullable', 'string'],
            'alergias' => ['sometimes', 'nullable', 'string'],
            'contacto_emergencia_nombre' => ['sometimes', 'nullable', 'string', 'max:150'],
            'contacto_emergencia_numero' => ['sometimes', 'nullable', 'string', 'max:30'],
            'foto_perfil' => ['sometimes', 'nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:10240'],
            'titulos' => ['sometimes', 'array'],
            'titulos.*.id' => ['nullable', 'integer'],
            'titulos.*.titulo' => ['nullable', 'string', 'max:150'],
            'titulos.*.entregado_por' => ['nullable', 'string', 'max:150'],
            'titulos.*.codigo_titulo' => ['nullable', 'string', 'max:100'],
            'titulos.*.archivo' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp,pdf', 'max:5120'],
            'titulos.*.archivos' => ['sometimes', 'array'],
            'titulos.*.archivos.*' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp,pdf', 'max:5120'],
            'titulos.*.archivo_ids' => ['sometimes', 'array'],
            'titulos.*.archivo_ids.*' => ['nullable', 'integer', 'exists:archivos,id'],
            'titulos.*.eliminar_archivo' => ['nullable', 'boolean'],
            'cursos' => ['sometimes', 'array'],
            'cursos.*.id' => ['nullable', 'integer'],
            'cursos.*.nombre_curso' => ['nullable', 'string', 'max:150'],
            'cursos.*.entregado_por' => ['nullable', 'string', 'max:150'],
            'cursos.*.codigo_curso' => ['nullable', 'string', 'max:100'],
            'cursos.*.archivo' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp,pdf', 'max:5120'],
            'cursos.*.archivos' => ['sometimes', 'array'],
            'cursos.*.archivos.*' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp,pdf', 'max:5120'],
            'cursos.*.archivo_ids' => ['sometimes', 'array'],
            'cursos.*.archivo_ids.*' => ['nullable', 'integer', 'exists:archivos,id'],
            'cursos.*.eliminar_archivo' => ['nullable', 'boolean'],
            'otros_documentos' => ['sometimes', 'array'],
            'otros_documentos.*.id' => ['nullable', 'integer'],
            'otros_documentos.*.nombre_documento' => ['nullable', 'string', 'max:150'],
            'otros_documentos.*.motivo' => ['nullable', 'string', 'max:255'],
            'otros_documentos.*.archivo' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp,pdf', 'max:5120'],
            'otros_documentos.*.archivos' => ['sometimes', 'array'],
            'otros_documentos.*.archivos.*' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp,pdf', 'max:5120'],
            'otros_documentos.*.archivo_ids' => ['sometimes', 'array'],
            'otros_documentos.*.archivo_ids.*' => ['nullable', 'integer', 'exists:archivos,id'],
            'otros_documentos.*.eliminar_archivo' => ['nullable', 'boolean'],
            'sanciones' => ['sometimes', 'array'],
            'sanciones.*.tipo_sancion' => ['nullable', 'string', 'max:150'],
            'sanciones.*.fecha' => ['nullable', 'date'],
            'sanciones.*.resumen_sancion' => ['nullable', 'string'],
            'sanciones.*.apelacion' => ['nullable', 'string'],
            'sanciones.*.fecha_apelacion' => ['nullable', 'date'],
            'sanciones.*.decision_cig' => ['nullable', 'string'],
            'reconocimiento' => ['sometimes', 'array'],
            'reconocimiento.servicio_extraordinario' => ['nullable', 'boolean'],
            'reconocimiento.abnegacion' => ['nullable', 'boolean'],
            'reconocimiento.medalla_honor_3' => ['nullable', 'boolean'],
            'reconocimiento.medalla_honor_2' => ['nullable', 'boolean'],
            'reconocimiento.medalla_honor_1' => ['nullable', 'boolean'],
            'reconocimiento.vittorio_cucchini' => ['nullable', 'boolean'],
            'reconocimiento.promesa' => ['nullable', 'boolean'],
            'reconocimiento.juramento' => ['nullable', 'boolean'],
        ]);

        $validated['titulos'] = $this->normalizeAttachmentRows(
            $validated['titulos'] ?? [],
            ['titulo', 'entregado_por', 'codigo_titulo'],
            'titulo',
            'titulos'
        );
        $validated['cursos'] = $this->normalizeAttachmentRows(
            $validated['cursos'] ?? [],
            ['nombre_curso', 'entregado_por', 'codigo_curso'],
            'nombre_curso',
            'cursos'
        );
        $validated['otros_documentos'] = $this->normalizeAttachmentRows(
            $validated['otros_documentos'] ?? [],
            ['nombre_documento', 'motivo'],
            'nombre_documento',
            'otros_documentos'
        );
        $validated['sanciones'] = $this->normalizeRows(
            $validated['sanciones'] ?? [],
            ['tipo_sancion', 'fecha', 'resumen_sancion', 'apelacion', 'fecha_apelacion', 'decision_cig'],
            'tipo_sancion',
            'sanciones'
        );
        $validated['reconocimiento'] = $this->normalizeRecognition($validated['reconocimiento'] ?? []);

        return $validated;
    }

    private function normalizeOptionalUserId(mixed $value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (is_numeric($value) === false) {
            return null;
        }

        $normalized = (int) $value;

        if ($normalized <= 0) {
            return null;
        }

        return User::query()->whereKey($normalized)->exists() ? $normalized : null;
    }

    private function buildMainPayload(array $validated, int $voluntarioId, ?HojaVidaAnual $existing = null): array
    {
        $cargoPayload = array_key_exists('cargo_clave', $validated)
            ? $this->resolveCargoPayload($validated['cargo_clave'] ?? null)
            : [
                'cargo_clave' => $existing?->cargo_clave,
                'cargo_nombre' => $existing?->cargo_nombre,
                'cargo_grupo' => $existing?->cargo_grupo,
                'cargo_direccion' => $existing?->cargo_direccion,
            ];

        $attendanceAdjustments = [
            'reuniones' => array_key_exists('asistencia_reuniones_filial_ajuste_horas', $validated)
                ? (float) ($validated['asistencia_reuniones_filial_ajuste_horas'] ?? 0)
                : (float) ($existing?->getRawOriginal('asistencia_reuniones_filial_ajuste_horas') ?? 0),
            'voluntariado' => array_key_exists('asistencia_actividades_voluntariado_ajuste_horas', $validated)
                ? (float) ($validated['asistencia_actividades_voluntariado_ajuste_horas'] ?? 0)
                : (float) ($existing?->getRawOriginal('asistencia_actividades_voluntariado_ajuste_horas') ?? 0),
            'filial' => array_key_exists('asistencia_horas_filial_ajuste_horas', $validated)
                ? (float) ($validated['asistencia_horas_filial_ajuste_horas'] ?? 0)
                : (float) ($existing?->getRawOriginal('asistencia_horas_filial_ajuste_horas') ?? 0),
            'formativas' => array_key_exists('asistencia_horas_formativas_ajuste_horas', $validated)
                ? (float) ($validated['asistencia_horas_formativas_ajuste_horas'] ?? 0)
                : (float) ($existing?->getRawOriginal('asistencia_horas_formativas_ajuste_horas') ?? 0),
        ];
        $unclassifiedHours = array_key_exists('asistencia_anual_ajuste_horas', $validated)
            ? (float) ($validated['asistencia_anual_ajuste_horas'] ?? 0)
            : (float) ($existing?->getRawOriginal('asistencia_anual_ajuste_horas') ?? 0);

        $attendance = HojaVidaAnual::calculateAttendanceMetricsForVoluntario(
            $voluntarioId,
            (int) $validated['anio'],
            $attendanceAdjustments,
            $unclassifiedHours
        );

        return [
            'voluntario_id' => $voluntarioId,
            'anio' => $validated['anio'],
            'asistencia_anual_horas' => $attendance['total'],
            'asistencia_anual_porcentaje' => $attendance['porcentaje'],
            'asistencia_anual_ajuste_horas' => $unclassifiedHours,
            'asistencia_reuniones_filial_ajuste_horas' => $attendanceAdjustments['reuniones'],
            'asistencia_actividades_voluntariado_ajuste_horas' => $attendanceAdjustments['voluntariado'],
            'asistencia_horas_filial_ajuste_horas' => $attendanceAdjustments['filial'],
            'asistencia_horas_formativas_ajuste_horas' => $attendanceAdjustments['formativas'],
            'cargo_clave' => $cargoPayload['cargo_clave'],
            'cargo_nombre' => $cargoPayload['cargo_nombre'],
            'cargo_grupo' => $cargoPayload['cargo_grupo'],
            'cargo_direccion' => $cargoPayload['cargo_direccion'],
            'estuvo_comision_servicio' => (bool) ($validated['estuvo_comision_servicio'] ?? false),
            'comision_fecha_inicio' => $validated['comision_fecha_inicio'] ?? null,
            'comision_fecha_termino' => $validated['comision_fecha_termino'] ?? null,
            'comision_lugar' => $this->normalizeText($validated['comision_lugar'] ?? null),
            'comision_actividad' => $this->normalizeText($validated['comision_actividad'] ?? null),
            'comentarios' => $this->normalizeText($validated['comentarios'] ?? null),
            'generada_por' => $validated['generada_por'] ?? $existing?->generada_por,
            'fecha_generacion' => $validated['fecha_generacion'] ?? $existing?->fecha_generacion ?? now()->toDateString(),
        ];
    }

    private function resolveCargoPayload(?string $cargoKey): array
    {
        $normalizedKey = trim((string) $cargoKey);

        if ($normalizedKey === '') {
            return [
                'cargo_clave' => null,
                'cargo_nombre' => null,
                'cargo_grupo' => null,
                'cargo_direccion' => null,
            ];
        }

        $definition = self::VOLUNTEER_CARGO_CATALOG[$normalizedKey] ?? null;

        if ($definition === null) {
            return [
                'cargo_clave' => null,
                'cargo_nombre' => null,
                'cargo_grupo' => null,
                'cargo_direccion' => null,
            ];
        }

        return [
            'cargo_clave' => $normalizedKey,
            'cargo_nombre' => $definition['nombre'],
            'cargo_grupo' => $definition['tipo'],
            'cargo_direccion' => $definition['direccion'],
        ];
    }

    private function syncRelations(HojaVidaAnual $hojaVidaAnual, array $validated, Request $request, bool $academicOnly = false): void
    {
        $this->syncAttachmentRelation(
            $hojaVidaAnual,
            $validated['titulos'],
            $request,
            'titulos',
            TituloVoluntario::class,
            ['titulo', 'entregado_por', 'codigo_titulo'],
            'titulo_voluntario',
            'respaldo_titulo',
            'hoja-vida/titulos'
        );
        $this->syncAttachmentRelation(
            $hojaVidaAnual,
            $validated['cursos'],
            $request,
            'cursos',
            CursoVoluntario::class,
            ['nombre_curso', 'entregado_por', 'codigo_curso'],
            'curso_voluntario',
            'respaldo_curso',
            'hoja-vida/cursos'
        );

        $this->syncAttachmentRelation(
            $hojaVidaAnual,
            $validated['otros_documentos'],
            $request,
            'otrosDocumentos',
            OtroDocumentoVoluntario::class,
            ['nombre_documento', 'motivo'],
            'otro_documento_voluntario',
            'respaldo_otro_documento',
            'hoja-vida/otros-documentos'
        );

        if ($academicOnly) {
            return;
        }

        $hojaVidaAnual->sanciones()->delete();

        if ($validated['sanciones'] !== []) {
            $hojaVidaAnual->sanciones()->createMany($validated['sanciones']);
        }

        $hojaVidaAnual->reconocimiento()->updateOrCreate(
            ['hoja_vida_anual_id' => $hojaVidaAnual->id],
            $validated['reconocimiento']
        );
    }

    private function syncAttachmentRelation(
        HojaVidaAnual $hojaVidaAnual,
        array $rows,
        Request $request,
        string $relation,
        string $modelClass,
        array $fields,
        string $entity,
        string $category,
        string $directory
    ): void {
        $existing = $hojaVidaAnual->{$relation}()->with('archivo')->get()->keyBy('id');
        $keptIds = [];

        foreach ($rows as $index => $row) {
            $record = null;

            if (! empty($row['id'])) {
                $record = $existing->get((int) $row['id']);

                if (! $record instanceof $modelClass) {
                    throw ValidationException::withMessages([
                        "{$relation}.{$index}.id" => 'El registro seleccionado no pertenece a esta hoja de vida.',
                    ]);
                }
            }

            $payload = [];

            foreach ($fields as $field) {
                $payload[$field] = $row[$field] ?? null;
            }

            if ($record) {
                $record->update($payload);
            } else {
                $record = $hojaVidaAnual->{$relation}()->create($payload);
            }

            $archivoId = $this->syncAttachmentFiles(
                $record,
                $row,
                $request,
                $relation,
                $index,
                $entity,
                $category,
                $directory
            );

            if ($record->archivo_id !== $archivoId) {
                $record->update(['archivo_id' => $archivoId]);
            }

            $keptIds[] = $record->id;
        }

        $existing
            ->except($keptIds)
            ->each(function (Model $record) {
                $this->deleteAttachmentRecord($record);
            });
    }

    private function syncAttachmentFiles(
        Model $record,
        array $row,
        Request $request,
        string $relation,
        int $index,
        string $entity,
        string $category,
        string $directory
    ): ?int {
        $archivosActuales = $record->relationLoaded('archivosAdjuntos')
            ? $record->getRelation('archivosAdjuntos')
            : $record->archivosAdjuntos()->get();

        $archivosActuales = $archivosActuales->keyBy('id');
        $archivoIds = collect($row['archivo_ids'] ?? [])
            ->filter(fn ($value) => $value !== null && $value !== '')
            ->map(fn ($value) => (int) $value)
            ->unique()
            ->values();

        $invalidIds = $archivoIds->diff($archivosActuales->keys());

        if ($invalidIds->isNotEmpty()) {
            throw ValidationException::withMessages([
                "{$relation}.{$index}.archivo_ids" => 'Uno o más archivos seleccionados no pertenecen a este registro.',
            ]);
        }

        $archivosActuales
            ->except($archivoIds->all())
            ->each(fn (Archivo $archivo) => $this->deleteArchivo($archivo));

        $nuevosArchivos = collect($row['archivos'] ?? [])
            ->filter()
            ->values();

        if ($nuevosArchivos->isNotEmpty()) {
            Storage::disk('public')->makeDirectory($directory);

            $nuevosArchivos->each(function ($file) use ($record, $request, $entity, $category, $directory) {
                $path = $file->store($directory, 'public');

                Archivo::create([
                    'entidad' => $entity,
                    'entidad_id' => $record->id,
                    'categoria' => $category,
                    'ruta' => $path,
                    'nombre_original' => $file->getClientOriginalName(),
                    'extension' => $file->getClientOriginalExtension(),
                    'mime_type' => $file->getClientMimeType(),
                    'tamano' => $file->getSize(),
                    'subido_por' => $request->user()?->id,
                ]);
            });
        }

        $archivoPrincipal = $record->archivosAdjuntos()->orderBy('id')->first();

        return $archivoPrincipal?->id;
    }

    private function deleteAttachmentRecord(Model $record): void
    {
        $archivos = $record->relationLoaded('archivosAdjuntos')
            ? $record->getRelation('archivosAdjuntos')
            : $record->archivosAdjuntos()->get();

        $archivos->each(fn (Archivo $archivo) => $this->deleteArchivo($archivo));

        $archivoPrincipal = $record->relationLoaded('archivo')
            ? $record->getRelation('archivo')
            : $record->archivo()->first();

        if ($archivoPrincipal instanceof Archivo && ! $archivos->contains('id', $archivoPrincipal->id)) {
            $this->deleteArchivo($archivoPrincipal);
        }

        $record->delete();
    }

    private function deleteArchivo(Archivo $archivo, bool $deleteModel = true): void
    {
        if ($archivo->ruta) {
            Storage::disk('public')->delete($archivo->ruta);
        }

        if ($deleteModel) {
            $archivo->delete();
        }
    }

    private function normalizeAttachmentRows(array $rows, array $fields, string $requiredField, string $prefix): array
    {
        $normalized = [];

        foreach ($rows as $index => $row) {
            $legacyArchivo = $row['archivo'] ?? null;
            $uploadedFiles = collect($row['archivos'] ?? [])
                ->filter()
                ->values();

            if ($legacyArchivo !== null) {
                $uploadedFiles->prepend($legacyArchivo);
            }

            $normalizedRow = [
                'id' => isset($row['id']) && $row['id'] !== '' ? (int) $row['id'] : null,
                'archivos' => $uploadedFiles->all(),
                'archivo_ids' => collect($row['archivo_ids'] ?? [])
                    ->filter(fn ($value) => $value !== null && $value !== '')
                    ->map(fn ($value) => (int) $value)
                    ->unique()
                    ->values()
                    ->all(),
                'eliminar_archivo' => filter_var($row['eliminar_archivo'] ?? false, FILTER_VALIDATE_BOOLEAN),
            ];

            if ($normalizedRow['eliminar_archivo']) {
                $normalizedRow['archivo_ids'] = [];
            }

            foreach ($fields as $field) {
                $normalizedRow[$field] = $this->normalizeText($row[$field] ?? null);
            }

            $hasAnyValue = collect($fields)
                ->map(fn (string $field) => $normalizedRow[$field])
                ->contains(fn ($value) => $value !== null);

            if (! $hasAnyValue && $normalizedRow['archivos'] === [] && $normalizedRow['archivo_ids'] === []) {
                continue;
            }

            if ($normalizedRow[$requiredField] === null) {
                throw ValidationException::withMessages([
                    "{$prefix}.{$index}.{$requiredField}" => 'Debes completar el campo principal de cada registro.',
                ]);
            }

            $normalized[] = $normalizedRow;
        }

        return $normalized;
    }

    private function normalizeRows(array $rows, array $fields, string $requiredField, string $prefix): array
    {
        $normalized = [];

        foreach ($rows as $index => $row) {
            $normalizedRow = [];

            foreach ($fields as $field) {
                $normalizedRow[$field] = $this->normalizeText($row[$field] ?? null);
            }

            $hasAnyValue = collect($normalizedRow)->contains(fn ($value) => $value !== null);

            if (! $hasAnyValue) {
                continue;
            }

            if ($normalizedRow[$requiredField] === null) {
                throw ValidationException::withMessages([
                    "{$prefix}.{$index}.{$requiredField}" => 'Debes completar el campo principal de cada registro.',
                ]);
            }

            $normalized[] = $normalizedRow;
        }

        return $normalized;
    }

    private function normalizeRecognition(array $recognition): array
    {
        return [
            'servicio_extraordinario' => (bool) ($recognition['servicio_extraordinario'] ?? false),
            'abnegacion' => (bool) ($recognition['abnegacion'] ?? false),
            'medalla_honor_3' => (bool) ($recognition['medalla_honor_3'] ?? false),
            'medalla_honor_2' => (bool) ($recognition['medalla_honor_2'] ?? false),
            'medalla_honor_1' => (bool) ($recognition['medalla_honor_1'] ?? false),
            'vittorio_cucchini' => (bool) ($recognition['vittorio_cucchini'] ?? false),
            'promesa' => (bool) ($recognition['promesa'] ?? false),
            'juramento' => (bool) ($recognition['juramento'] ?? false),
        ];
    }

    private function isVolunteerSelfServiceRequest(Request $request, int $voluntarioId): bool
    {
        $user = $request->user()?->loadMissing('roles', 'voluntario');

        if (! $user || ! $user->hasRole('voluntario') || ! $user->voluntario) {
            return false;
        }

        if ($user->hasRole('administrador') || $user->hasRole('secretario-directiva')) {
            return false;
        }

        return (int) $user->voluntario->id === $voluntarioId;
    }

    private function limitVolunteerEditableSections(Request $request, HojaVidaAnual $hojaVidaAnual): void
    {
        $request->merge([
            'anio' => $hojaVidaAnual->anio,
            'asistencia_anual_ajuste_horas' => $hojaVidaAnual->asistencia_anual_ajuste_horas,
            'asistencia_reuniones_filial_ajuste_horas' => $hojaVidaAnual->asistencia_reuniones_filial_ajuste_horas,
            'asistencia_actividades_voluntariado_ajuste_horas' => $hojaVidaAnual->asistencia_actividades_voluntariado_ajuste_horas,
            'asistencia_horas_filial_ajuste_horas' => $hojaVidaAnual->asistencia_horas_filial_ajuste_horas,
            'asistencia_horas_formativas_ajuste_horas' => $hojaVidaAnual->asistencia_horas_formativas_ajuste_horas,
            'estuvo_comision_servicio' => $hojaVidaAnual->estuvo_comision_servicio,
            'comision_fecha_inicio' => optional($hojaVidaAnual->comision_fecha_inicio)->toDateString(),
            'comision_fecha_termino' => optional($hojaVidaAnual->comision_fecha_termino)->toDateString(),
            'comision_lugar' => $hojaVidaAnual->comision_lugar,
            'comision_actividad' => $hojaVidaAnual->comision_actividad,
            'comentarios' => $hojaVidaAnual->comentarios,
            'cargo_clave' => $hojaVidaAnual->cargo_clave,
            'generada_por' => $hojaVidaAnual->generada_por,
            'fecha_generacion' => optional($hojaVidaAnual->fecha_generacion)->toDateString(),
        ]);

        $request->request->remove('sanciones');
        $request->request->remove('reconocimiento');
    }


    private function syncVolunteerProfile(Voluntario $voluntario, array $validated, Request $request): void
    {
        $profilePayload = $this->extractVolunteerProfilePayload($validated);

        if ($profilePayload !== []) {
            $voluntario->update($profilePayload);
        }

        if ($request->hasFile('foto_perfil')) {
            $this->syncVolunteerPhoto($request, $voluntario);
        }
    }

    private function extractVolunteerProfilePayload(array $validated): array
    {
        $fields = [
            'registro_filial',
            'filial_id',
            'rut',
            'nombres',
            'apellidos',
            'nacionalidad',
            'fecha_nacimiento',
            'fecha_incorporacion',
            'nivel_escolaridad',
            'estado_civil',
            'ocupacion',
            'grupo_sanguineo',
            'correo_electronico',
            'celular',
            'domicilio',
            'enfermedades',
            'alergias',
            'contacto_emergencia_nombre',
            'contacto_emergencia_numero',
        ];

        $payload = [];

        foreach ($fields as $field) {
            if (array_key_exists($field, $validated)) {
                $payload[$field] = $validated[$field];
            }
        }

        return $payload;
    }

    private function syncVolunteerPhoto(Request $request, Voluntario $voluntario): void
    {
        $file = $request->file('foto_perfil');

        if (! $file) {
            return;
        }

        $archivoActual = $voluntario->archivoFotoPerfil()->first();

        Storage::disk('public')->makeDirectory('voluntarios/fotos');

        $optimized = $this->imageOptimizer->store($file, 'voluntarios/fotos', 2 * 1024 * 1024);

        $voluntario->archivoFotoPerfil()->updateOrCreate(
            [
                'entidad' => self::VOLUNTEER_PROFILE_ENTITY,
                'entidad_id' => $voluntario->id,
                'categoria' => self::VOLUNTEER_PROFILE_PHOTO_CATEGORY,
            ],
            [
                'ruta' => $optimized['path'],
                'nombre_original' => $optimized['original_name'],
                'extension' => $optimized['extension'],
                'mime_type' => $optimized['mime_type'],
                'tamano' => $optimized['size'],
                'subido_por' => $request->user()?->id,
            ]
        );

        if ($archivoActual?->ruta && $archivoActual->ruta !== $optimized['path']) {
            Storage::disk('public')->delete($archivoActual->ruta);
        }
    }
    private function normalizeText(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $normalized = trim((string) $value);

        return $normalized === '' ? null : $normalized;
    }
}






