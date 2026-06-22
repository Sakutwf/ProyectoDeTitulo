<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\User;
use App\Models\Voluntario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    private const USER_RELATIONS = [
        'roles.permissions',
        'voluntario.hojaDeVida.hojasAnuales',
        'voluntario.hojaDeVida.antecedentes',
        'actividades.evento',
        'registrosHorasFilial',
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
                $subQuery->where('nombre', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('rut', 'like', "%{$search}%");
            });
        }

        $users = $query->orderBy('id')->paginate(8);
        $users->through(fn (User $user) => $this->prepareUserResponse($user, true));

        return response()->json($users, 200);
    }

    /**
     * Metodo para buscar un usuario por su rut.
     */
    public function search(Request $request)
    {
        $users = User::with(self::USER_RELATIONS)
                ->where('rut', $request->rut)
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
            ? ['nullable', 'string', 'min:6', 'required_without:contrasena']
            : ['nullable', 'string', 'min:6'];

        $contrasenaRules = $userId === null
            ? ['nullable', 'string', 'min:6', 'required_without:password']
            : ['nullable', 'string', 'min:6'];

        $validated = $request->validate([
            'rut' => ['required', 'string', Rule::unique('users', 'rut')->ignore($userId)],
            'nombre' => ['required', 'string'],
            'email' => ['required', 'email', Rule::unique('users', 'email')->ignore($userId)],
            'telefono' => ['required', 'string'],
            'estado' => ['required', 'string'],
            'password' => $passwordRules,
            'contrasena' => $contrasenaRules,
            'roles' => ['sometimes', 'array'],
            'roles.*' => ['integer', Rule::exists('roles', 'id')],
        ]);

        $password = $validated['password'] ?? $validated['contrasena'] ?? null;

        unset($validated['password'], $validated['contrasena']);

        if ($password !== null) {
            $validated['password'] = $password;
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

        $voluntarioData = $request->only([
            'fecha_ingreso',
            'n_registro',
            'factor_rh',
            'grupo_sanguineo',
            'fecha_nacimiento',
        ]);

        $fotoRules = ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'];

        $validated = validator(array_merge(
            optional($user->voluntario)->only([
                'fecha_ingreso',
                'n_registro',
                'factor_rh',
                'grupo_sanguineo',
                'fecha_nacimiento',
            ]) ?? [],
            $voluntarioData,
            ['foto_perfil' => $request->file('foto_perfil')]
        ), [
            'fecha_ingreso' => ['required', 'date'],
            'n_registro' => [
                'required',
                'string',
                Rule::unique('voluntarios', 'n_registro')->ignore(optional($user->voluntario)->id),
            ],
            'factor_rh' => ['required', 'string'],
            'grupo_sanguineo' => ['required', 'string'],
            'fecha_nacimiento' => ['required', 'date'],
            'foto_perfil' => $fotoRules,
        ])->validate();

        unset($validated['foto_perfil']);

        if ($voluntario instanceof Voluntario) {
            $voluntario->update($validated);
            $this->syncVoluntarioPhoto($request, $voluntario);

            if ($voluntario->hojaDeVida) {
                $voluntario->hojaDeVida->update([
                    'estado' => $user->estado,
                ]);
            } else {
                $voluntario->hojaDeVida()->create([
                    'fecha_creacion' => now()->toDateString(),
                    'estado' => $user->estado,
                ]);
            }

            return;
        }

        $voluntario = $user->voluntario()->create($validated);
        $this->syncVoluntarioPhoto($request, $voluntario);
        $voluntario->hojaDeVida()->create([
            'fecha_creacion' => now()->toDateString(),
            'estado' => $user->estado,
        ]);
    }

    private function hasVolunteerRole(User $user, Request $request): bool
    {
        if ($request->exists('roles')) {
            $roleIds = $request->input('roles', []);

            return Role::query()
                ->whereIn('id', $roleIds)
                ->where('slug', 'voluntario')
                ->exists();
        }

        $user->loadMissing('roles');

        return $user->roles->contains(fn (Role $role) => $role->slug === 'voluntario');
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
}
