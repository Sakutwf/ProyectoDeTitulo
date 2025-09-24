<?php

namespace App\Http\Controllers;

use App\Models\Historial;
use Illuminate\Http\Request;

class HistorialController extends Controller
{
    /**
     * Mostrar todos los historiales paginados (8 por página)
     * Permite búsqueda por user_id, año, cargo, taller, curso, seminario, titulos, premios, estudios
     */
    public function index(Request $request){
    // 1) Empieza la consulta, ya con Eager‐Loading
    $query = Historial::with(['actividades','titulos','premios']);

    // 2) Aplica búsqueda si existe
    if ($request->filled('search')) {
        $search = $request->search;
        $query->where(function($q) use ($search) {
            $q->where('user_id', 'like', "%$search%")
              ->orWhere('anio', 'like', "%$search%")
              // … resto de filtros …
              ->orWhere('estudios', 'like', "%$search%");
        });
    }

    // 3) Ordena y pagina
    $historiales = $query
        ->orderBy('id', 'asc')
        ->paginate(8);

    return response()->json($historiales, 200);
    }


    /**
     * Mostrar un historial específico
     */
    public function show($id){

    $historial = Historial::with(['actividades','titulos','premios'])
                          ->findOrFail($id);

    return response()->json($historial, 200);
    }


    /**
     * Crear un nuevo historial
     */
    public function store(Request $request)
    {
        try {
            $historial = new Historial();
            $historial->user_id = $request->user_id;
            $historial->año = $request->año;
            $historial->cargo = $request->cargo;
            $historial->taller = $request->taller;
            $historial->curso = $request->curso;
            $historial->seminario = $request->seminario;
            $historial->porcentaje_de_asistencia = $request->porcentaje_de_asistencia;
            $historial->titulos = $request->titulos;
            $historial->premios = $request->premios;
            $historial->observaciones = $request->observaciones;
            $historial->estudios = $request->estudios;
            $historial->save();
            return response()->json($historial, 201);
        } catch (\Exception $e) {
            return response()->json($e->getMessage(), 400);
        }
    }

    /**
     * Actualizar un historial existente
     */
    public function update(Request $request, $id)
    {
        $historial = Historial::findOrFail($id);
        $historial->user_id = $request->user_id;
        $historial->año = $request->año;
        $historial->cargo = $request->cargo;
        $historial->taller = $request->taller;
        $historial->curso = $request->curso;
        $historial->seminario = $request->seminario;
        $historial->porcentaje_de_asistencia = $request->porcentaje_de_asistencia;
        $historial->titulos = $request->titulos;
        $historial->premios = $request->premios;
        $historial->observaciones = $request->observaciones;
        $historial->estudios = $request->estudios;
        $historial->save();
        return response()->json($historial, 200);
    }

    /**
     * Eliminar un historial
     */
    public function destroy($id)
    {
        $historial = Historial::findOrFail($id);
        $historial->delete();
        return response()->json(true, 200);
    }

    // Opcional: método para buscar por user_id
    public function searchByUser(Request $request)
    {
        $userId = $request->user_id;
        $historiales = Historial::where('user_id', $userId)->get();
        return response()->json($historiales, 200);
    }

    public function getByUserId($id)
    {
        $historiales = \App\Models\Historial::where('user_id', $id)->get();
        return response()->json($historiales, 200);
    }
}
