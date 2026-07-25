<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    private const USER_RELATIONS = [
        'roles.permissions',
        'voluntario.filial',
        'voluntario.hojaVidaAnual.titulos',
        'voluntario.hojaVidaAnual.cursos',
        'voluntario.hojaVidaAnual.otrosDocumentos',
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

    public function forgotPassword(Request $request)
    {
        $data = $request->validate([
            'rut' => ['required', 'string', 'max:150'],
        ]);

        try {
            Password::broker()->sendResetLink([
                'username' => trim($data['rut']),
            ]);
        } catch (\Throwable $exception) {
            report($exception);

            return response()->json([
                'message' => 'El servicio de correo no está disponible en este momento.',
            ], 503);
        }

        return response()->json([
            'message' => 'Si el RUT está registrado y tiene un correo asociado, enviaremos un enlace de recuperación.',
        ]);
    }

    public function resetPassword(Request $request)
    {
        $data = $request->validate([
            'rut' => ['required', 'string', 'max:150'],
            'token' => ['required', 'string'],
            'password' => ['required', 'string', 'min:8', 'max:255', 'confirmed'],
        ]);

        $status = Password::broker()->reset([
            'username' => trim($data['rut']),
            'token' => $data['token'],
            'password' => $data['password'],
        ], function (User $user, string $password): void {
            $user->forceFill([
                'password' => $password,
                'must_change_password' => false,
            ])->save();

            $user->tokens()->delete();
            event(new PasswordReset($user));
        });

        if ($status !== Password::PASSWORD_RESET) {
            return response()->json([
                'message' => 'El enlace de recuperación no es válido o ya expiró.',
                'errors' => ['token' => ['Solicita un nuevo enlace de recuperación.']],
            ], 422);
        }

        return response()->json([
            'message' => 'Contraseña actualizada correctamente.',
        ]);
    }

    public function sendUserResetLink(Request $request, User $user)
    {
        abort_unless($request->user()?->hasRole('administrador'), 403);

        if (blank($user->email)) {
            return response()->json([
                'message' => 'El perfil no tiene un correo de recuperación registrado.',
            ], 422);
        }

        try {
            $status = Password::broker()->sendResetLink([
                'username' => $user->username,
            ]);
        } catch (\Throwable $exception) {
            report($exception);

            return response()->json([
                'message' => 'El servicio de correo no está configurado.',
            ], 503);
        }

        if ($status !== Password::RESET_LINK_SENT) {
            return response()->json([
                'message' => 'No se pudo enviar el enlace de recuperación.',
            ], 422);
        }

        return response()->json([
            'message' => 'Enlace de recuperación enviado.',
        ]);
    }

    public function generateUserResetLink(Request $request, User $user)
    {
        abort_unless($request->user()?->hasRole('administrador'), 403);

        $token = Password::broker()->createToken($user);
        $query = http_build_query([
            'token' => $token,
            'rut' => $user->username,
        ]);

        return response()->json([
            'url' => rtrim((string) config('app.frontend_url'), '/') . "/recuperar-contrasena?{$query}",
            'expires_in_minutes' => (int) config('auth.passwords.users.expire'),
        ]);
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
