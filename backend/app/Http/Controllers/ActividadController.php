<?php

namespace App\Http\Controllers;

use App\Enums\ActividadTipo;
use App\Enums\EventoTipo;
use App\Models\Actividad;
use App\Models\Evento;
use App\Services\AttendanceSheetService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Rule;

class ActividadController extends Controller
{
    public function __construct(private AttendanceSheetService $attendanceSheetService)
    {
    }

    /**
     * Display a listing of the resource paginated (8 per page) and searchable.
     */
    public function index(Request $request)
    {
        $query = Actividad::with(['evento', 'users']);

        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function ($subQuery) use ($search) {
                $subQuery->where('tipo', 'like', "%{$search}%")
                    ->orWhere('nombre', 'like', "%{$search}%")
                    ->orWhereHas('evento', function ($eventoQuery) use ($search) {
                        $eventoQuery->where('nombre', 'like', "%{$search}%")
                            ->orWhere('tipo', 'like', "%{$search}%");
                    });
            });
        }

        return response()->json($query->orderBy('id')->paginate(8), 200);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'evento_id' => 'required|integer|exists:eventos,id',
            'nombre' => 'required|string|max:255',
            'tipo' => ['required', Rule::enum(ActividadTipo::class)],
            'N_beneficiarios' => 'nullable|integer',
            'horas_participacion' => 'nullable|numeric|min:0',
        ]);
        $this->ensureEventAndActivityTypesAreCompatible((int) $request->evento_id, (string) $request->tipo);

        try {
            $actividad = new Actividad();
            $actividad->evento_id = $request->evento_id;
            $actividad->nombre = trim((string) $request->nombre);
            $actividad->tipo = ActividadTipo::tryFromMixed($request->tipo);
            $actividad->N_beneficiarios = $request->N_beneficiarios;
            $actividad->horas_participacion = $request->horas_participacion ?? 1;
            $actividad->save();

            return response()->json($actividad->load('users'), 201);
        } catch (\Exception $exception) {
            return response()->json(['error' => $exception->getMessage()], 400);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        return response()->json(Actividad::with('evento', 'users')->findOrFail($id), 200);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Actividad $actividad)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update($id, Request $request)
    {
        $request->validate([
            'evento_id' => 'sometimes|integer|exists:eventos,id',
            'nombre' => 'sometimes|string|max:255',
            'tipo' => ['sometimes', Rule::enum(ActividadTipo::class)],
            'N_beneficiarios' => 'nullable|integer',
            'horas_participacion' => 'nullable|numeric|min:0',
            'planilla' => 'sometimes|array',
            'planilla.*' => 'integer|exists:users,id',
            'planilla_detalle' => 'sometimes|array',
            'planilla_detalle.*.user_id' => 'required_with:planilla_detalle|integer|exists:users,id',
            'planilla_detalle.*.asistio' => 'nullable|boolean',
        ]);

        try {
            $actividad = Actividad::findOrFail($id);
            $affectedUserIds = $actividad->users()->pluck('users.id')->all();
            $eventoId = (int) ($request->evento_id ?? $actividad->evento_id);
            $tipo = $request->tipo ?? $actividad->tipo;

            $this->ensureEventAndActivityTypesAreCompatible($eventoId, $tipo);

            $actividad->evento_id = $request->evento_id ?? $actividad->evento_id;
            $actividad->nombre = $request->has('nombre')
                ? trim((string) $request->nombre)
                : $actividad->nombre;
            $actividad->tipo = $request->has('tipo')
                ? ActividadTipo::tryFromMixed($request->tipo)
                : $actividad->tipo;
            $actividad->N_beneficiarios = $request->N_beneficiarios ?? $actividad->N_beneficiarios;
            $actividad->horas_participacion = $request->horas_participacion ?? $actividad->horas_participacion;
            $actividad->save();

            $updatedUserIds = $this->syncPlanilla($actividad, $request);
            $this->attendanceSheetService->syncForUsers(array_merge($affectedUserIds, $updatedUserIds));

            return response()->json($actividad->load('users'), 200);
        } catch (\Exception $exception) {
            \Log::error('Error al asociar voluntarios: ' . $exception->getMessage());

            return response()->json(['error' => $exception->getMessage()], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $actividad = Actividad::findOrFail($id);
        $actividad->delete();

        return response()->json(true, 200);
    }

    /**
     * Asociar un voluntario a la actividad.
     */
    public function asociarVoluntario($id, Request $request)
    {
        $actividad = Actividad::findOrFail($id);
        $actividad->users()->syncWithoutDetaching([
            $request->user_id => ['asistio' => true],
        ]);
        $this->attendanceSheetService->syncForUsers([$request->user_id]);

        return response()->json(['success' => true], 200);
    }

    /**
     * Desasociar un voluntario de la actividad.
     */
    public function desasociarVoluntario($id, Request $request)
    {
        $actividad = Actividad::findOrFail($id);
        $userId = (int) $request->user_id;
        $actividad->users()->detach($request->user_id);
        $this->attendanceSheetService->syncForUsers([$userId]);

        return response()->json(['success' => true], 200);
    }

    private function syncPlanilla(Actividad $actividad, Request $request): array
    {
        if ($request->has('planilla_detalle')) {
            $syncData = collect($request->input('planilla_detalle', []))
                ->mapWithKeys(fn (array $detalle) => [
                    (int) $detalle['user_id'] => ['asistio' => (bool) ($detalle['asistio'] ?? true)],
                ])
                ->all();

            $actividad->users()->sync($syncData);

            return array_map('intval', array_keys($syncData));
        }

        if ($request->has('planilla')) {
            $syncData = collect($request->input('planilla', []))
                ->mapWithKeys(fn ($userId) => [(int) $userId => ['asistio' => true]])
                ->all();

            $actividad->users()->sync($syncData);

            return array_map('intval', array_keys($syncData));
        }

        return $actividad->users()->pluck('users.id')->map(fn ($userId) => (int) $userId)->all();
    }

    private function ensureEventAndActivityTypesAreCompatible(int $eventoId, mixed $tipo): void
    {
        $evento = Evento::findOrFail($eventoId);
        $tipoEvento = EventoTipo::tryFromMixed($evento->tipo);
        $tipoActividad = ActividadTipo::tryFromMixed($tipo);

        if ($tipoEvento === EventoTipo::FORMATIVO && ! $tipoActividad?->isFormativa()) {
            throw ValidationException::withMessages([
                'tipo' => ['Las actividades de eventos FORMATIVOS solo pueden ser CURSO, TALLER o SEMINARIO.'],
            ]);
        }

        if ($tipoEvento === EventoTipo::SERVICIO && ! $tipoActividad?->isServicio()) {
            throw ValidationException::withMessages([
                'tipo' => ['Las actividades de eventos SERVICIO solo pueden ser CAMPAÑA, OPERATIVO, COBERTURA o COMUNITARIA.'],
            ]);
        }
    }
}
