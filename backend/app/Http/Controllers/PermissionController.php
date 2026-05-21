<?php

namespace App\Http\Controllers;

use App\Models\Permission;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class PermissionController extends Controller
{
    public function index()
    {
        return response()->json(Permission::with('roles')->orderBy('name')->get(), 200);
    }

    public function store(Request $request)
    {
        $data = $this->validatePermission($request);

        $permission = Permission::create($data);

        return response()->json($permission->load('roles'), 201);
    }

    public function show(Permission $permission)
    {
        return response()->json($permission->load('roles'), 200);
    }

    public function update(Request $request, Permission $permission)
    {
        $data = $this->validatePermission($request, $permission->id);

        $permission->update($data);

        return response()->json($permission->load('roles'), 200);
    }

    public function destroy(Permission $permission)
    {
        $permission->delete();

        return response()->json(null, 204);
    }

    private function validatePermission(Request $request, ?int $permissionId = null): array
    {
        $validated = $request->validate([
            'name' => ['required', 'string', Rule::unique('permissions', 'name')->ignore($permissionId)],
            'slug' => ['nullable', 'string', Rule::unique('permissions', 'slug')->ignore($permissionId)],
            'description' => ['nullable', 'string'],
        ]);

        $validated['slug'] = $validated['slug'] ?? Str::slug($validated['name']);

        return $validated;
    }
}
