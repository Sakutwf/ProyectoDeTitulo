<?php

namespace App\Http\Controllers;

use App\Enums\EventoTipo;
use App\Models\Evento;
use App\Services\AttendanceSheetService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class EventoController extends Controller
{
    public function __construct(private AttendanceSheetService $attendanceSheetService)
    {
    }

    /**
     * Display a listing of the resource paginated (8 per page) and searchable.
     */
    public function index(Request $request)
    {
        $query = Evento::query();

        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('nombre', 'like', "%$search%")
                  ->orWhere('tipo', 'like', "%$search%")
                  ->orWhere('Descripcion', 'like', "%$search%");
            });
        }

        $eventos = $query->orderBy('id', 'asc')->paginate(8);

        return response()->json($eventos, 200);
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
        $data = $this->validateEvento($request);

        try {
            $evento = new Evento();
            $evento->nombre = $data['nombre'];
            $evento->fecha_inicio = $data['fecha_inicio'];
            $evento->fecha_termino = $data['fecha_termino'];
            $evento->descripcion = $data['descripcion'];
            $evento->tipo = EventoTipo::tryFromMixed($data['tipo']);
            $evento->save();
            return response()->json($evento, 201);
        } catch (\Exception $e) {
            return response()->json($e->getMessage(), 400);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        return response()->json(Evento::findOrFail($id), 200);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Evento $evento)
    {
        //
    }

    /**
         * Update the specified resource in storage.
     */
    public function update($id, Request $request)
    {
        $data = $this->validateEvento($request);
        $evento = Evento::findOrFail($id);
        $affectedUserIds = $this->getAffectedUserIds($evento);
        $evento->nombre = $data['nombre'];
        $evento->fecha_inicio = $data['fecha_inicio'];
        $evento->fecha_termino = $data['fecha_termino'];
        $evento->descripcion = $data['descripcion'];
        $evento->tipo = EventoTipo::tryFromMixed($data['tipo']);
        $evento->save();
        $this->attendanceSheetService->syncForUsers($affectedUserIds);
        return response()->json($evento, 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $evento = Evento::findOrFail($id);
        $affectedUserIds = $this->getAffectedUserIds($evento);
        $evento = $evento->delete(); // Soft delete
        $this->attendanceSheetService->syncForUsers($affectedUserIds);
        return response()->json($evento, 200);
    }

    private function getAffectedUserIds(Evento $evento): array
    {
        return $evento->load('actividades.users')
            ->actividades
            ->flatMap(fn ($actividad) => $actividad->users->pluck('id'))
            ->map(fn ($userId) => (int) $userId)
            ->unique()
            ->values()
            ->all();
    }

    private function validateEvento(Request $request): array
    {
        return $request->validate([
            'nombre' => ['required', 'string'],
            'fecha_inicio' => ['required', 'date'],
            'fecha_termino' => ['required', 'date', 'after_or_equal:fecha_inicio'],
            'descripcion' => ['nullable', 'string'],
            'tipo' => ['required', Rule::enum(EventoTipo::class)],
        ]);
    }

}
