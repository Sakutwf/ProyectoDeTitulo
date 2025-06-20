<?php

namespace App\Http\Controllers;

use App\Models\Actividad;
use App\Models\Histactividad;
use Illuminate\Http\Request;

class ActividadController extends Controller
{
    /**
     * Display a listing of the resource paginated (8 per page) and searchable.
     */
    public function index(Request $request)
    {
        $query = Actividad::with(['evento', 'users']); // <-- Agrega 'users'

        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                // Eliminar búsqueda por planilla string
                $q->orWhere('tipo', 'like', "%$search%")
                  ->orWhereHas('evento', function($qe) use ($search) {
                      $qe->where('nombre', 'like', "%$search%")
                         ->orWhere('tipo', 'like', "%$search%");
                  });
            });
        }

        $actividades = $query->orderBy('id', 'asc')->paginate(8);

        return response()->json($actividades, 200);
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
        \Log::info('Datos recibidos en store actividad:', $request->all()); // <-- Línea para depuración

        $validated = $request->validate([
            'evento_id' => 'required|integer|exists:eventos,id',
            'tipo' => 'required|string',
            'N_beneficiarios' => 'nullable|integer', // Cambiado a nullable
        ]);

        try {
            $actividad = new Actividad();
            $actividad->evento_id = $request->evento_id;
            $actividad->tipo = $request->tipo;
            $actividad->N_beneficiarios = $request->N_beneficiarios; // Puede ser null
            $actividad->save();

            // No se asignan usuarios en la creación

            return response()->json($actividad->load('users'), 201);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $actividad = Actividad::with('evento', 'users')->findOrFail($id);
        return response()->json($actividad, 200);
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
        try {
            $actividad = Actividad::findOrFail($id);
            $actividad->evento_id = $request->evento_id ?? $actividad->evento_id;
            $actividad->tipo = $request->tipo ?? $actividad->tipo;
            $actividad->N_beneficiarios = $request->N_beneficiarios ?? $actividad->N_beneficiarios;
            $actividad->save();

            // Sincroniza usuarios si se reciben
            if ($request->has('planilla')) {
                $actividad->users()->sync($request->planilla);

                // Registrar en histactividades para cada usuario asociado
                $evento = $actividad->evento; // Relación evento
                $fecha_inicio = $evento ? $evento->fecha_inicio : now();
                foreach ($request->planilla as $user_id) {
                    Histactividad::firstOrCreate([
                        'user_id' => $user_id,
                        'nombre' => $actividad->tipo,
                        'fecha_inicio' => $fecha_inicio
                    ]);
                }
            }

            return response()->json($actividad->load('users'), 200);
        } catch (\Exception $e) {
            \Log::error('Error al asociar voluntarios: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $actividad = Actividad::findOrFail($id);
        $actividad = $actividad->delete();  // Realiza el soft delete

        return response()->json($actividad, 200);  // Responde con éxito
    }

    /**
     * Asociar un voluntario a la actividad.
     */
    public function asociarVoluntario($id, Request $request)
    {
        $actividad = Actividad::findOrFail($id);
        $actividad->users()->attach($request->user_id);

        // Registrar en histactividades
        $evento = $actividad->evento;
        $fecha_inicio = $evento ? $evento->fecha_inicio : now();
        Histactividad::firstOrCreate([
            'user_id' => $request->user_id,
            'nombre' => $actividad->tipo,
            'fecha_inicio' => $fecha_inicio
        ]);

        return response()->json(['success' => true], 200);
    }

    /**
     * Desasociar un voluntario de la actividad.
     */
    public function desasociarVoluntario($id, Request $request)
    {
        $actividad = Actividad::findOrFail($id);
        $actividad->users()->detach($request->user_id);
        return response()->json(['success' => true], 200);
    }
}
