<?php

namespace App\Http\Controllers;

use App\Models\Archivo;
use App\Models\CursoVoluntario;
use App\Models\HojaVidaAnual;
use App\Models\TituloVoluntario;
use App\Models\User;
use App\Models\Voluntario;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class HojaVidaAnualController extends Controller
{
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
        'voluntario.user.roles.permissions',
        'titulos.archivo',
        'cursos.archivo',
        'sanciones',
        'reconocimiento',
        'generador',
    ];

    public function index(Voluntario $voluntario)
    {
        return response()->json(
            $voluntario->hojaVidaAnual()
                ->with(['titulos.archivo', 'cursos.archivo', 'sanciones', 'reconocimiento', 'generador'])
                ->orderByDesc('anio')
                ->get(),
            200
        );
    }

    public function store(Request $request, Voluntario $voluntario)
    {
        $validated = $this->validateRequest($request, $voluntario->id);

        $hojaVidaAnual = DB::transaction(function () use ($validated, $voluntario, $request) {
            $record = HojaVidaAnual::create(
                $this->buildMainPayload($validated, $voluntario->id)
            );

            $this->syncRelations($record, $validated, $request);

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
        $validated = $this->validateRequest($request, $hojaVidaAnual->voluntario_id, $hojaVidaAnual);

        $hojaVidaAnual = DB::transaction(function () use ($validated, $hojaVidaAnual, $request) {
            $hojaVidaAnual->update(
                $this->buildMainPayload($validated, $hojaVidaAnual->voluntario_id, $hojaVidaAnual)
            );

            $this->syncRelations($hojaVidaAnual, $validated, $request);

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
            'titulos' => ['sometimes', 'array'],
            'titulos.*.id' => ['nullable', 'integer'],
            'titulos.*.titulo' => ['nullable', 'string', 'max:150'],
            'titulos.*.entregado_por' => ['nullable', 'string', 'max:150'],
            'titulos.*.codigo_titulo' => ['nullable', 'string', 'max:100'],
            'titulos.*.archivo' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp,pdf', 'max:5120'],
            'titulos.*.eliminar_archivo' => ['nullable', 'boolean'],
            'cursos' => ['sometimes', 'array'],
            'cursos.*.id' => ['nullable', 'integer'],
            'cursos.*.nombre_curso' => ['nullable', 'string', 'max:150'],
            'cursos.*.entregado_por' => ['nullable', 'string', 'max:150'],
            'cursos.*.codigo_curso' => ['nullable', 'string', 'max:100'],
            'cursos.*.archivo' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp,pdf', 'max:5120'],
            'cursos.*.eliminar_archivo' => ['nullable', 'boolean'],
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

        $attendance = HojaVidaAnual::calculateAttendanceMetricsForVoluntario(
            $voluntarioId,
            (int) $validated['anio'],
            $attendanceAdjustments
        );

        return [
            'voluntario_id' => $voluntarioId,
            'anio' => $validated['anio'],
            'asistencia_anual_horas' => $attendance['total'],
            'asistencia_anual_porcentaje' => $attendance['porcentaje'],
            'asistencia_anual_ajuste_horas' => 0,
            'asistencia_reuniones_filial_ajuste_horas' => $attendanceAdjustments['reuniones'],
            'asistencia_actividades_voluntariado_ajuste_horas' => $attendanceAdjustments['voluntariado'],
            'asistencia_horas_filial_ajuste_horas' => $attendanceAdjustments['filial'],
            'asistencia_horas_formativas_ajuste_horas' => $attendanceAdjustments['formativas'],
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

    private function syncRelations(HojaVidaAnual $hojaVidaAnual, array $validated, Request $request): void
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

            $archivoId = $this->syncAttachmentFile(
                $record,
                $row,
                $request,
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

    private function syncAttachmentFile(
        Model $record,
        array $row,
        Request $request,
        string $entity,
        string $category,
        string $directory
    ): ?int {
        $archivoActual = $record->relationLoaded('archivo')
            ? $record->getRelation('archivo')
            : $record->archivo()->first();

        if (($row['eliminar_archivo'] ?? false) && $archivoActual instanceof Archivo) {
            $this->deleteArchivo($archivoActual);
            $archivoActual = null;
        }

        if (! isset($row['archivo'])) {
            return $archivoActual?->id;
        }

        if ($archivoActual instanceof Archivo) {
            $this->deleteArchivo($archivoActual, false);
        }

        Storage::disk('public')->makeDirectory($directory);

        $file = $row['archivo'];
        $path = $file->store($directory, 'public');

        $archivo = $archivoActual instanceof Archivo ? $archivoActual : new Archivo();
        $archivo->fill([
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
        $archivo->save();

        return $archivo->id;
    }

    private function deleteAttachmentRecord(Model $record): void
    {
        $archivo = $record->relationLoaded('archivo')
            ? $record->getRelation('archivo')
            : $record->archivo()->first();

        if ($archivo instanceof Archivo) {
            $this->deleteArchivo($archivo);
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
            $normalizedRow = [
                'id' => isset($row['id']) && $row['id'] !== '' ? (int) $row['id'] : null,
                'archivo' => $row['archivo'] ?? null,
                'eliminar_archivo' => filter_var($row['eliminar_archivo'] ?? false, FILTER_VALIDATE_BOOLEAN),
            ];

            foreach ($fields as $field) {
                $normalizedRow[$field] = $this->normalizeText($row[$field] ?? null);
            }

            $hasAnyValue = collect($fields)
                ->map(fn (string $field) => $normalizedRow[$field])
                ->contains(fn ($value) => $value !== null);

            if (! $hasAnyValue && ! $normalizedRow['archivo']) {
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

    private function normalizeText(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $normalized = trim((string) $value);

        return $normalized === '' ? null : $normalized;
    }
}




