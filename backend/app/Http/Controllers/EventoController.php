<?php

namespace App\Http\Controllers;

use App\Models\Evento;
use Illuminate\Http\Request;

class EventoController extends Controller
{
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
        try {
            $evento = new Evento();
            $evento->nombre = $request->nombre;
            $evento->fecha_inicio = $request->fecha_inicio;
            $evento->fecha_termino = $request->fecha_termino;
            $evento->descripcion = $request->descripcion;
            $evento->tipo = $request->tipo;
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
        $evento = Evento::findOrFail($id);
        $evento->nombre = $request->nombre;
        $evento->fecha_inicio = $request->fecha_inicio;
        $evento->fecha_termino = $request->fecha_termino;
        $evento->descripcion = $request->descripcion;
        $evento->tipo = $request->tipo;
        $evento->save();
        return response()->json($evento, 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $evento = Evento::findOrFail($id);
        $evento = $evento->delete(); // Soft delete
        return response()->json($evento, 200);
    }
}
