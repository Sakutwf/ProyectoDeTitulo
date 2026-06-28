<?php

namespace App\Http\Controllers;

use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class RoleController extends Controller
{
    public function index()
    {
        return response()->json(Role::with('permissions')->orderBy('nombre')->get(), 200);
    }

    public function store(Request $request)
    {
        $data = $this->validateRole($request);

        $role = Role::create($data);
        $role->permissions()->sync($request->input('permissions', []));

        return response()->json($role->load('permissions'), 201);
    }

    public function show(Role $role)
    {
        return response()->json($role->load('permissions'), 200);
    }

    public function update(Request $request, Role $role)
    {
        $data = $this->validateRole($request, $role->id);

        $role->update($data);

        if ($request->exists('permissions')) {
            $role->permissions()->sync($request->input('permissions', []));
        }

        return response()->json($role->load('permissions'), 200);
    }

    public function destroy(Role $role)
    {
        $role->delete();

        return response()->json(null, 204);
    }

    private function validateRole(Request $request, ?int $roleId = null): array
    {
        return $request->validate([
            'nombre' => ['required', 'string', 'max:100'],
            'clave' => ['required', 'string', 'max:100', Rule::unique('roles', 'clave')->ignore($roleId)],
            'permissions' => ['sometimes', 'array'],
            'permissions.*' => ['integer', Rule::exists('permissions', 'id')],
        ]);
    }
}
