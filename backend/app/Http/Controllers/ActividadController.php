<?php

namespace App\Http\Controllers;

use App\Models\Actividad;
use Illuminate\Http\Request;

class ActividadController extends Controller
{
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
            'tipo' => 'required|string',
            'N_beneficiarios' => 'nullable|integer',
        ]);

        try {
            $actividad = new Actividad();
            $actividad->evento_id = $request->evento_id;
            $actividad->tipo = $request->tipo;
            $actividad->N_beneficiarios = $request->N_beneficiarios;
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
        try {
            $actividad = Actividad::findOrFail($id);
            $actividad->evento_id = $request->evento_id ?? $actividad->evento_id;
            $actividad->tipo = $request->tipo ?? $actividad->tipo;
            $actividad->N_beneficiarios = $request->N_beneficiarios ?? $actividad->N_beneficiarios;
            $actividad->save();

            if ($request->has('planilla')) {
                $actividad->users()->sync($request->planilla);
            }

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
        $actividad->users()->attach($request->user_id);

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
