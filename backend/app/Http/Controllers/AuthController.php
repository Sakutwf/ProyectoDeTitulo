<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    private const USER_RELATIONS = [
        'roles.permissions',
        'voluntario.filial',
        'voluntario.hojasVidaAnuales.titulos',
        'voluntario.hojasVidaAnuales.cursos',
        'voluntario.hojasVidaAnuales.sanciones',
        'voluntario.hojasVidaAnuales.reconocimiento',
    ];

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'user' => ['required', 'string'],
            'id' => ['required', 'string'],
        ], [], [
            'user' => 'usuario',
            'id' => 'identificador',
        ]);

        $identifier = trim($credentials['user']);
        $secret = $credentials['id'];

        $user = User::with(self::USER_RELATIONS)
            ->where(function ($query) use ($identifier) {
                $query->whereHas('voluntario', function ($voluntarioQuery) use ($identifier) {
                    $voluntarioQuery->where('n_registro', $identifier);
                });

                if (filter_var($identifier, FILTER_VALIDATE_EMAIL)) {
                    $query->orWhere('email', $identifier);
                }
            })
            ->first();

        if (! $user instanceof User || ! Hash::check($secret, $user->password)) {
            throw ValidationException::withMessages([
                'user' => 'Las credenciales ingresadas no son validas.',
            ]);
        }

        if (! $user->estado) {
            throw ValidationException::withMessages([
                'user' => 'La cuenta se encuentra inactiva y no puede iniciar sesion.',
            ]);
        }

        $isAdmin = $user->hasRole('administrador');
        $isVolunteer = $user->voluntario !== null;

        return response()->json([
            'user' => $this->prepareUserResponse($user),
            'roles' => $user->roles->pluck('clave')->values(),
            'is_admin' => $isAdmin,
            'can_access_admin' => $isAdmin || $user->hasRole('secretario-directiva'),
            'can_access_volunteer' => $isVolunteer,
            'requires_access_selection' => $isAdmin && $isVolunteer,
        ], 200);
    }

    private function prepareUserResponse(User $user): User
    {
        $user->loadMissing('roles.permissions');

        return $user;
    }
}
