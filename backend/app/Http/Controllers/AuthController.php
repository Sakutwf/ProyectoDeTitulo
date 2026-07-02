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
        'voluntario.hojaVidaAnual.titulos',
        'voluntario.hojaVidaAnual.cursos',
        'voluntario.hojaVidaAnual.sanciones',
        'voluntario.hojaVidaAnual.reconocimiento',
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
            ->where('username', $identifier)
            ->first();

        if (! $user instanceof User || ! Hash::check($secret, $user->password)) {
            throw ValidationException::withMessages([
                'user' => 'Las credenciales ingresadas no son validas.',
            ]);
        }

        $isAdmin = $user->hasRole('administrador');
        $isVolunteer = $user->voluntario !== null;
        $user->tokens()->where('name', 'frontend')->delete();
        $token = $user->createToken('frontend')->plainTextToken;

        return response()->json([
            'user' => $this->prepareUserResponse($user),
            'token' => $token,
            'roles' => $user->roles->pluck('clave')->values(),
            'is_admin' => $isAdmin,
            'can_access_admin' => $isAdmin || $user->hasRole('secretario-directiva'),
            'can_access_volunteer' => $isVolunteer,
            'requires_access_selection' => $isAdmin && $isVolunteer,
            'must_change_password' => (bool) $user->must_change_password,
        ], 200);
    }

    public function logout(Request $request)
    {
        $request->user()?->currentAccessToken()?->delete();

        return response()->json(null, 204);
    }

    private function prepareUserResponse(User $user): User
    {
        $user->loadMissing('roles.permissions');

        return $user;
    }
}
