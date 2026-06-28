<?php

namespace App\Http\Controllers;

use App\Models\Actividad;
use App\Models\Archivo;
use App\Models\BoletaViatico;
use App\Models\Voluntario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class ActividadController extends Controller
{
    private const RELATIONS = ['filial', 'creador', 'voluntarios.user'];

    public function index(Request $request)
    {
        $query = Actividad::with(self::RELATIONS);

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

        return response()->json($actividad->load(self::RELATIONS), 201);
    }

    public function show($id)
    {
        return response()->json(Actividad::with(self::RELATIONS)->findOrFail($id), 200);
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

        return response()->json($actividad->load(self::RELATIONS), 200);
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

        return response()->json($actividad->load(self::RELATIONS), 200);
    }

    public function desasociarVoluntario($id, Request $request)
    {
        $actividad = Actividad::findOrFail($id);
        $data = $request->validate([
            'voluntario_id' => ['required', 'integer', 'exists:voluntarios,id'],
        ]);

        $actividad->voluntarios()->detach($data['voluntario_id']);

        return response()->json($actividad->load(self::RELATIONS), 200);
    }

    public function galeria($id)
    {
        $actividad = Actividad::findOrFail($id);

        return response()->json(
            $actividad->galeria()
                ->with(['archivo', 'subidoPor.voluntario'])
                ->latest('fecha')
                ->latest('id')
                ->get(),
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
            'voluntarios' => ['sometimes', 'array'],
            'voluntarios.*' => ['integer', 'exists:voluntarios,id'],
            'voluntarios_detalle' => ['sometimes', 'array'],
            'voluntarios_detalle.*.voluntario_id' => ['required_with:voluntarios_detalle', 'integer', 'exists:voluntarios,id'],
            'voluntarios_detalle.*.horas_asistidas' => ['nullable', 'numeric', 'min:0'],
            'voluntarios_detalle.*.registrado_por' => ['nullable', 'integer', 'exists:users,id'],
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
}



