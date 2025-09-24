<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

/**
 * Controlador para el modelo User
 * Todos los metodos tienen que tener un formato de respuesta json
 * return response()->json(data, 200);
 */

class UserController extends Controller
{
    /**
     * Metodo para devolver todos los usuarios paginados (8 por página)
     * Permite búsqueda por nombre, email o rut usando el parámetro 'search'
     * @return response json
     */
    public function index(Request $request)
    {
        $query = User::query();

        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('nombre', 'like', "%$search%")
                  ->orWhere('email', 'like', "%$search%")
                  ->orWhere('rut', 'like', "%$search%");
            });
        }

        $users = $query->orderBy('id', 'asc')->paginate(8);

        return response()->json($users, 200);
    }

    /**
     * Metodo para buscar un usuario por su rut
     */
    public function search(Request $request)
    {
        return response()->json(User::where('rut', $request->rut)->get(), 200);
    }

    /**
     * Metodo para crear un nuevo usuario
     */
    public function store(Request $request)
    {
        try{
            $user = new User();
            $user->rut = $request->rut;
            $user->nombre = $request->nombre;
            $user->email = $request->email;
            $user->telefono = $request->telefono;
            $user->fecha_nacimiento = $request->fecha_nacimiento;
            $user->fecha_ingreso = $request->fecha_ingreso;
            $user->grupo_sanguineo = $request->grupo_sanguineo;
            $user->factor_rh = $request->factor_rh;
            $user->password = bcrypt($request->password);
            $user->role_id = $request->role_id;
            $user->filial_id = $request->filial_id;
            $user->save();
            return response()->json($user, 201);
        }catch(\Exception $e){
            return response()->json($e->getMessage(), 400);
        }
    }

    /**
     * Metodo para devolver un usuario
     */
    public function show($id)
    {
        return response()->json(User::findOrFail($id), 200);
    }

    /**
     * Metodo para actualizar un usuario
     */
    public function update($id, Request $request)
    {
        $user = User::findOrFail($id);
        $user->rut = $request->rut;
        $user->nombre = $request->nombre;
        $user->email = $request->email;
        $user->telefono = $request->telefono;
        $user->fecha_nacimiento = $request->fecha_nacimiento;
        $user->fecha_ingreso = $request->fecha_ingreso;
        $user->grupo_sanguineo = $request->grupo_sanguineo;
        $user->factor_rh = $request->factor_rh;
        $user->password = bcrypt($request->password);
        $user->role_id = $request->role_id;
        $user->filial_id = $request->filial_id;
        $user->save();
        return response()->json($user, 200);
    }

    //Inician los metodos de softDeletes

    /**
     * Metodo para marca un registro como eliminado (Soft Delete)
     */
    public function destroy($id)
    {
        $user = User::findOrFail($id);
        $user = $user->delete();  // Realiza el soft delete

        return response()->json($user, 200);  // Responde con éxito
    }

}
