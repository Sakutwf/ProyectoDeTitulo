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
        $query = Actividad::with('evento');

        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('planilla', 'like', "%$search%")
                  ->orWhere('tipo', 'like', "%$search%")
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
        try {
            $actividad = new Actividad();
            $actividad->evento_id = $request->evento_id;
            $actividad->planilla = $request->planilla;
            $actividad->tipo = $request->tipo;
            $actividad->n_beneficiarios = $request->n_beneficiarios;
            $actividad->save();
            return response()->json($actividad, 201);
        } catch (\Exception $e) {
            return response()->json($e->getMessage(), 400);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $actividad = Actividad::with('evento')->findOrFail($id);
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
        $actividad = Actividad::findOrFail($id);
        $actividad->evento_id = $request->evento_id;
        $actividad->planilla = $request->planilla;
        $actividad->tipo = $request->tipo;
        $actividad->N_beneficiarios = $request->N_beneficiarios;
        $actividad->save();
        return response()->json($actividad, 200);
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
}
