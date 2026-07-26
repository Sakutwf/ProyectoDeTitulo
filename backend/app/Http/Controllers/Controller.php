<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

abstract class Controller
{
    protected function requireAnyRole(Request $request, array $roles): void
    {
        $user = $request->user()?->loadMissing('roles');

        abort_unless(
            $user?->roles->contains(fn ($role) => in_array($role->clave, $roles, true)),
            403,
            'No tienes permisos para realizar esta acción.'
        );
    }
}
