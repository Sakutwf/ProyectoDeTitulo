<?php

namespace App\Http\Controllers;

use App\Models\Filial;
use App\Models\Role;
use App\Models\User;
use App\Models\Voluntario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class UserController extends Controller
{
    private const DEFAULT_PROFILE_PASSWORD = 'cruzRojaCco26';

    private const USER_RELATIONS = [
        'roles.permissions',
        'voluntario.filial',
        'voluntario.hojasVidaAnuales.titulos',
        'voluntario.hojasVidaAnuales.cursos',
        'voluntario.hojasVidaAnuales.sanciones',
        'voluntario.hojasVidaAnuales.reconocimiento',
    ];

    /**
     * Metodo para devolver todos los usuarios paginados (8 por pagina).
     */
    public function index(Request $request)
    {
        $query = User::with(self::USER_RELATIONS);

        if ($request->filled('search')) {
            $search = $request->search;

            $query->where(function ($subQuery) use ($search) {
                $subQuery->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhereHas('voluntario', function ($voluntarioQuery) use ($search) {
                        $voluntarioQuery
                            ->where('rut', 'like', "%{$search}%")
                            ->orWhere('n_registro', 'like', "%{$search}%")
                            ->orWhere('nombres', 'like', "%{$search}%")
                            ->orWhere('apellidos', 'like', "%{$search}%");
                    });
            });
        }

        $users = $query->orderBy('id')->paginate(8);
        $users->through(fn (User $user) => $this->prepareUserResponse($user, true));

        return response()->json($users, 200);
    }

    /**
     * Busca usuarios por nombre, email, rut o numero de registro.
     */
    public function search(Request $request)
    {
        $request->validate([
            'q' => ['nullable', 'string'],
            'rut' => ['nullable', 'string'],
        ]);

        $term = trim((string) ($request->input('q') ?? $request->input('rut') ?? ''));

        $users = User::with(self::USER_RELATIONS)
                ->where(function ($query) use ($term) {
                    $query->where('name', 'like', "%{$term}%")
                        ->orWhere('email', 'like', "%{$term}%")
                        ->orWhereHas('voluntario', function ($voluntarioQuery) use ($term) {
                            $voluntarioQuery
                                ->where('rut', 'like', "%{$term}%")
                                ->orWhere('n_registro', 'like', "%{$term}%")
                                ->orWhere('nombres', 'like', "%{$term}%")
                                ->orWhere('apellidos', 'like', "%{$term}%");
                        });
                })
                ->get()
                ->map(fn (User $user) => $this->prepareUserResponse($user, true));

        return response()->json($users, 200);
    }

    /**
     * Metodo para crear un nuevo usuario.
     */
    public function store(Request $request)
    {
        $data = $this->validateUser($request);

        $user = DB::transaction(function () use ($request, $data) {
            $user = User::create($data);
            $this->syncRoles($user, $request);
            $this->syncVoluntario($user, $request);

            return $this->prepareUserResponse($user->load(self::USER_RELATIONS), true);
        });

        return response()->json($user, 201);
    }

    /**
     * Metodo para devolver un usuario.
     */
    public function show(User $user)
    {
        return response()->json(
            $this->prepareUserResponse($user->load(self::USER_RELATIONS), true),
            200
        );
    }

    /**
     * Metodo para actualizar un usuario.
     */
    public function update(Request $request, User $user)
    {
        $data = $this->validateUser($request, $user->id);

        $user = DB::transaction(function () use ($request, $user, $data) {
            $user->update($data);
            $this->syncRoles($user, $request);
            $this->syncVoluntario($user, $request);

            return $this->prepareUserResponse($user->load(self::USER_RELATIONS), true);
        });

        return response()->json($user, 200);
    }

    public function updateVolunteerPhoto(Request $request, User $user)
    {
        $request->validate([
            'foto_perfil' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ]);

        $voluntario = $user->voluntario;

        if (! $voluntario instanceof Voluntario) {
            return response()->json([
                'message' => 'El usuario no tiene ficha de voluntario.',
            ], 422);
        }

        $this->syncVoluntarioPhoto($request, $voluntario);

        return response()->json(
            $this->prepareUserResponse($user->fresh()->load(self::USER_RELATIONS), true),
            200
        );
    }

    /**
     * Metodo para eliminar un usuario.
     */
    public function destroy(User $user)
    {
        $user->delete();

        return response()->json(null, 204);
    }

    private function validateUser(Request $request, ?int $userId = null): array
    {
        $passwordRules = $userId === null
            ? ['nullable', 'string', 'min:6']
            : ['nullable', 'string', 'min:6'];

        $contrasenaRules = $userId === null
            ? ['nullable', 'string', 'min:6']
            : ['nullable', 'string', 'min:6'];

        $validated = $request->validate([
            'name' => ['nullable', 'string', 'max:150'],
            'email' => ['required', 'email', Rule::unique('users', 'email')->ignore($userId)],
            'estado' => ['required', 'boolean'],
            'password' => $passwordRules,
            'contrasena' => $contrasenaRules,
            'roles' => ['sometimes', 'array'],
            'roles.*' => ['integer', Rule::exists('roles', 'id')],
        ]);

        $password = $validated['password'] ?? $validated['contrasena'] ?? null;

        unset($validated['password'], $validated['contrasena']);

        if (blank($validated['name'] ?? null)) {
            $derivedName = trim(implode(' ', array_filter([
                (string) $request->input('nombres', ''),
                (string) $request->input('apellidos', ''),
            ])));

            if ($derivedName !== '') {
                $validated['name'] = $derivedName;
            } elseif ($userId === null) {
                throw ValidationException::withMessages([
                    'name' => 'Debes indicar un nombre para el perfil.',
                ]);
            }
        }

        if ($password !== null) {
            $validated['password'] = $password;
        } elseif ($userId === null) {
            $validated['password'] = self::DEFAULT_PROFILE_PASSWORD;
        }

        return $validated;
    }

    private function syncRoles(User $user, Request $request): void
    {
        if ($request->exists('roles')) {
            $user->roles()->sync($request->input('roles', []));
            $user->load('roles.permissions');
        }
    }

    private function syncVoluntario(User $user, Request $request): void
    {
        if (! $this->hasVolunteerRole($user, $request)) {
            return;
        }

        $voluntario = $user->voluntario;

        $validated = validator(
            array_merge($request->all(), ['foto_perfil' => $request->file('foto_perfil')]),
            $this->voluntarioRules($voluntario?->n_registro)
        )->validate();

        unset($validated['foto_perfil']);

        if ($voluntario instanceof Voluntario) {
            $voluntario->update($validated);
            $this->syncUserDisplayNameFromVolunteer($user, $validated);
            $this->syncVoluntarioPhoto($request, $voluntario);

            return;
        }

        $voluntario = $user->voluntario()->create($validated + ['user_id' => $user->id]);
        $this->syncUserDisplayNameFromVolunteer($user, $validated);
        $this->syncVoluntarioPhoto($request, $voluntario);
    }

    private function hasVolunteerRole(User $user, Request $request): bool
    {
        if ($request->exists('roles')) {
            $roleIds = $request->input('roles', []);

            return Role::query()
                ->whereIn('id', $roleIds)
                ->where('clave', 'voluntario')
                ->exists();
        }

        $user->loadMissing('roles');

        return $user->roles->contains(fn (Role $role) => $role->clave === 'voluntario');
    }

    private function prepareUserResponse(User $user, bool $includeArchivedVolunteerProfile = false): User
    {
        if (! $includeArchivedVolunteerProfile && ! $user->hasRole('voluntario')) {
            $user->setRelation('voluntario', null);
        }

        return $user;
    }

    private function syncVoluntarioPhoto(Request $request, Voluntario $voluntario): void
    {
        if (! $request->hasFile('foto_perfil')) {
            return;
        }

        if ($voluntario->foto_perfil) {
            Storage::disk('public')->delete($voluntario->foto_perfil);
        }

        Storage::disk('public')->makeDirectory('voluntarios/fotos');

        $path = $request->file('foto_perfil')->store('voluntarios/fotos', 'public');
        $voluntario->update(['foto_perfil' => $path]);
    }

    private function voluntarioRules(?string $currentRegistro = null): array
    {
        return [
            'n_registro' => ['required', 'string', 'max:30', Rule::unique('voluntarios', 'n_registro')->ignore($currentRegistro, 'n_registro')],
            'filial_id' => ['required', 'integer', Rule::exists('filiales', 'id')],
            'rut' => ['required', 'string', 'max:20', Rule::unique('voluntarios', 'rut')->ignore($currentRegistro, 'n_registro')],
            'nombres' => ['required', 'string', 'max:150'],
            'apellidos' => ['required', 'string', 'max:150'],
            'nacionalidad' => ['nullable', 'string', 'max:100'],
            'fecha_nacimiento' => ['nullable', 'date'],
            'fecha_incorporacion' => ['nullable', 'date'],
            'celular' => ['nullable', 'string', 'max:30'],
            'domicilio' => ['nullable', 'string', 'max:255'],
            'enfermedades' => ['nullable', 'string'],
            'alergias' => ['nullable', 'string'],
            'contacto_emergencia_nombre' => ['nullable', 'string', 'max:150'],
            'contacto_emergencia_numero' => ['nullable', 'string', 'max:30'],
            'foto_perfil' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ];
    }

    private function syncUserDisplayNameFromVolunteer(User $user, array $voluntarioData): void
    {
        $fullName = trim(implode(' ', array_filter([
            $voluntarioData['nombres'] ?? '',
            $voluntarioData['apellidos'] ?? '',
        ])));

        if ($fullName !== '' && $user->name !== $fullName) {
            $user->update(['name' => $fullName]);
        }
    }
}
