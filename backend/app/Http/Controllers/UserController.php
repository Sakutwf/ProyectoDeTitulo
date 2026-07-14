<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\User;
use App\Models\Voluntario;
use App\Services\ImageOptimizer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class UserController extends Controller
{
    public function __construct(private readonly ImageOptimizer $imageOptimizer) {}

    private const DEFAULT_PROFILE_PASSWORD = 'cruzRojaCco26';

    private const VOLUNTEER_CARGO_CATALOG = [
        'gobernanza_presidente' => ['tipo' => 'Gobernanza', 'nombre' => 'Presidente', 'direccion' => null],
        'gobernanza_vicepresidente' => ['tipo' => 'Gobernanza', 'nombre' => 'Vicepresidente', 'direccion' => null],
        'gobernanza_secretario' => ['tipo' => 'Gobernanza', 'nombre' => 'Secretario', 'direccion' => null],
        'gobernanza_finanzas' => ['tipo' => 'Gobernanza', 'nombre' => 'Finanzas', 'direccion' => null],
        'directorio_director_salud' => ['tipo' => 'Directorio', 'nombre' => 'Director', 'direccion' => 'Salud'],
        'directorio_director_subrogante_salud' => ['tipo' => 'Directorio', 'nombre' => 'Director Subrogante', 'direccion' => 'Salud'],
        'directorio_director_juventud' => ['tipo' => 'Directorio', 'nombre' => 'Director', 'direccion' => 'Juventud'],
        'directorio_director_subrogante_juventud' => ['tipo' => 'Directorio', 'nombre' => 'Director Subrogante', 'direccion' => 'Juventud'],
        'directorio_director_gestion' => ['tipo' => 'Directorio', 'nombre' => 'Director', 'direccion' => 'Gestion'],
        'directorio_director_subrogante_gestion' => ['tipo' => 'Directorio', 'nombre' => 'Director Subrogante', 'direccion' => 'Gestion'],
        'directorio_director_desarrollo' => ['tipo' => 'Directorio', 'nombre' => 'Director', 'direccion' => 'Desarrollo'],
        'directorio_director_subrogante_desarrollo' => ['tipo' => 'Directorio', 'nombre' => 'Director Subrogante', 'direccion' => 'Desarrollo'],
        'directorio_director_bienestar_social' => ['tipo' => 'Directorio', 'nombre' => 'Director', 'direccion' => 'Bienestar Social'],
        'directorio_director_subrogante_bienestar_social' => ['tipo' => 'Directorio', 'nombre' => 'Director Subrogante', 'direccion' => 'Bienestar Social'],
        'directorio_director_comunicaciones' => ['tipo' => 'Directorio', 'nombre' => 'Director', 'direccion' => 'Comunicaciones'],
        'directorio_director_subrogante_comunicaciones' => ['tipo' => 'Directorio', 'nombre' => 'Director Subrogante', 'direccion' => 'Comunicaciones'],
    ];

    private const USER_RELATIONS = [
        'roles.permissions',
        'voluntario.filial',
        'voluntario.archivoFotoPerfil',
        'voluntario.actividades.filial',
        'voluntario.hojaVidaAnual.titulos.archivo',
        'voluntario.hojaVidaAnual.titulos.archivosAdjuntos',
        'voluntario.hojaVidaAnual.cursos.archivo',
        'voluntario.hojaVidaAnual.cursos.archivosAdjuntos',
        'voluntario.hojaVidaAnual.sanciones',
        'voluntario.hojaVidaAnual.reconocimiento',
    ];

    public function index(Request $request)
    {
        $query = User::with(self::USER_RELATIONS);

        if ($request->filled('search')) {
            $search = $request->search;

            $query->where(function ($subQuery) use ($search) {
                $subQuery->where('username', 'like', "%{$search}%")
                    ->orWhereHas('voluntario', function ($voluntarioQuery) use ($search) {
                        $voluntarioQuery
                            ->where('rut', 'like', "%{$search}%")
                            ->orWhere('registro_filial', 'like', "%{$search}%")
                            ->orWhere('nombres', 'like', "%{$search}%")
                            ->orWhere('apellidos', 'like', "%{$search}%")
                            ->orWhere('correo_electronico', 'like', "%{$search}%");
                    })
                    ->orWhereHas('roles', function ($roleQuery) use ($search) {
                        $roleQuery->where('nombre', 'like', "%{$search}%");
                    });
            });
        }

        $users = $query->orderBy('id')->paginate(8);
        $users->through(fn (User $user) => $this->prepareUserResponse($user, true));

        return response()->json($users, 200);
    }

    public function search(Request $request)
    {
        $request->validate([
            'q' => ['nullable', 'string'],
            'rut' => ['nullable', 'string'],
        ]);

        $term = trim((string) ($request->input('q') ?? $request->input('rut') ?? ''));

        $users = User::with(self::USER_RELATIONS)
            ->where(function ($query) use ($term) {
                $query->where('username', 'like', "%{$term}%")
                    ->orWhereHas('voluntario', function ($voluntarioQuery) use ($term) {
                        $voluntarioQuery
                            ->where('rut', 'like', "%{$term}%")
                            ->orWhere('registro_filial', 'like', "%{$term}%")
                            ->orWhere('nombres', 'like', "%{$term}%")
                            ->orWhere('apellidos', 'like', "%{$term}%")
                            ->orWhere('correo_electronico', 'like', "%{$term}%");
                    })
                    ->orWhereHas('roles', function ($roleQuery) use ($term) {
                        $roleQuery->where('nombre', 'like', "%{$term}%");
                    });
            })
            ->get()
            ->map(fn (User $user) => $this->prepareUserResponse($user, true));

        return response()->json($users, 200);
    }

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

    public function show(User $user)
    {
        return response()->json(
            $this->prepareUserResponse($user->load(self::USER_RELATIONS), true),
            200
        );
    }

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
            'foto_perfil' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:10240'],
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

    public function destroy(User $user)
    {
        $user->delete();

        return response()->json(null, 204);
    }

    private function validateUser(Request $request, ?int $userId = null): array
    {
        $passwordRules = ['nullable', 'string', 'min:6'];
        $contrasenaRules = ['nullable', 'string', 'min:6'];

        $validated = $request->validate([
            'username' => ['nullable', 'string', 'max:150', Rule::unique('users', 'username')->ignore($userId)],
            'correo_notificaciones' => ['sometimes', 'nullable', 'email', 'max:255'],
            'must_change_password' => ['sometimes', 'boolean'],
            'password' => $passwordRules,
            'contrasena' => $contrasenaRules,
            'roles' => ['sometimes', 'array'],
            'roles.*' => ['integer', Rule::exists('roles', 'id')],
        ]);

        $password = $validated['password'] ?? $validated['contrasena'] ?? null;
        unset($validated['password'], $validated['contrasena']);

        $rut = trim((string) $request->input('rut', ''));

        if ($rut !== '') {
            $validated['username'] = $rut;
        } elseif (blank($validated['username'] ?? null) && $userId === null) {
            throw ValidationException::withMessages([
                'username' => 'Debes indicar un username para el perfil administrativo.',
            ]);
        }

        if ($password !== null) {
            $validated['password'] = $password;
        } elseif ($userId === null) {
            $validated['password'] = self::DEFAULT_PROFILE_PASSWORD;
        }

        if (! array_key_exists('must_change_password', $validated) && $userId === null) {
            $validated['must_change_password'] = true;
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
            $this->voluntarioRules($voluntario?->id)
        )->validate();

        unset($validated['foto_perfil']);

        if ($voluntario instanceof Voluntario) {
            $voluntario->update($validated);
            $this->syncUsernameFromVolunteer($user, $validated);
            $this->syncVoluntarioPhoto($request, $voluntario);
            $this->syncVoluntarioCargo($request, $voluntario);

            return;
        }

        $voluntario = $user->voluntario()->create($validated + ['user_id' => $user->id]);
        $this->syncUsernameFromVolunteer($user, $validated);
        $this->syncVoluntarioPhoto($request, $voluntario);
        $this->syncVoluntarioCargo($request, $voluntario);
    }

    private function syncVoluntarioCargo(Request $request, Voluntario $voluntario): void
    {
        $cargoKey = trim((string) $request->input('cargo_clave', ''));

        if ($cargoKey === '') {
            return;
        }

        $definition = self::VOLUNTEER_CARGO_CATALOG[$cargoKey] ?? null;

        if ($definition === null) {
            return;
        }

        if (! Schema::hasTable('tipos_cargo') || ! Schema::hasTable('direcciones') || ! Schema::hasTable('cargos') || ! Schema::hasTable('cargo_voluntario')) {
            return;
        }

        $timestamp = now();
        $today = $timestamp->toDateString();
        $fechaInicio = trim((string) $request->input('fecha_incorporacion', '')) ?: $today;

        $tipoId = DB::table('tipos_cargo')
            ->where('nombre', $definition['tipo'])
            ->value('id');

        if (! $tipoId) {
            $tipoId = DB::table('tipos_cargo')->insertGetId([
                'nombre' => $definition['tipo'],
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ]);
        }

        $direccionId = null;

        if (! empty($definition['direccion'])) {
            $direccionId = DB::table('direcciones')
                ->where('nombre', $definition['direccion'])
                ->value('id');

            if (! $direccionId) {
                $direccionId = DB::table('direcciones')->insertGetId([
                    'nombre' => $definition['direccion'],
                    'descripcion' => null,
                    'created_at' => $timestamp,
                    'updated_at' => $timestamp,
                ]);
            }
        }

        $cargoQuery = DB::table('cargos')
            ->where('tipo_cargo_id', $tipoId)
            ->where('nombre', $definition['nombre']);

        if ($direccionId) {
            $cargoQuery->where('direccion_id', $direccionId);
        } else {
            $cargoQuery->whereNull('direccion_id');
        }

        $cargoId = $cargoQuery->value('id');

        if (! $cargoId) {
            $cargoId = DB::table('cargos')->insertGetId([
                'tipo_cargo_id' => $tipoId,
                'direccion_id' => $direccionId,
                'nombre' => $definition['nombre'],
                'descripcion' => null,
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ]);
        }

        $currentAssignment = DB::table('cargo_voluntario')
            ->where('voluntario_id', $voluntario->id)
            ->whereNull('fecha_termino')
            ->orderByDesc('fecha_inicio')
            ->first();

        if ($currentAssignment && (int) $currentAssignment->cargo_id === (int) $cargoId) {
            return;
        }

        DB::table('cargo_voluntario')
            ->where('voluntario_id', $voluntario->id)
            ->whereNull('fecha_termino')
            ->update([
                'fecha_termino' => $today,
                'updated_at' => $timestamp,
            ]);

        DB::table('cargo_voluntario')->insert([
            'voluntario_id' => $voluntario->id,
            'cargo_id' => $cargoId,
            'fecha_inicio' => $fechaInicio,
            'fecha_termino' => null,
            'observaciones' => null,
            'created_at' => $timestamp,
            'updated_at' => $timestamp,
        ]);
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

        $archivoActual = $voluntario->archivoFotoPerfil()->first();

        Storage::disk('public')->makeDirectory('voluntarios/fotos');

        $file = $request->file('foto_perfil');
        $optimized = $this->imageOptimizer->store($file, 'voluntarios/fotos', 2 * 1024 * 1024);

        $voluntario->archivoFotoPerfil()->updateOrCreate(
            [
                'entidad' => Voluntario::ENTITY_TYPE,
                'entidad_id' => $voluntario->id,
                'categoria' => Voluntario::PROFILE_PHOTO_CATEGORY,
            ],
            [
                'ruta' => $optimized['path'],
                'nombre_original' => $optimized['original_name'],
                'extension' => $optimized['extension'],
                'mime_type' => $optimized['mime_type'],
                'tamano' => $optimized['size'],
                'subido_por' => $request->user()?->id,
            ]
        );

        if ($archivoActual?->ruta && $archivoActual->ruta !== $optimized['path']) {
            Storage::disk('public')->delete($archivoActual->ruta);
        }
    }

    private function voluntarioRules(?int $currentVoluntarioId = null): array
    {
        return [
            'registro_filial' => ['required', 'string', 'max:50'],
            'filial_id' => ['required', 'integer', Rule::exists('filiales', 'id')],
            'rut' => ['required', 'string', 'max:20', Rule::unique('voluntarios', 'rut')->ignore($currentVoluntarioId)],
            'nombres' => ['required', 'string', 'max:150'],
            'apellidos' => ['required', 'string', 'max:150'],
            'nacionalidad' => ['nullable', 'string', 'max:100'],
            'fecha_nacimiento' => ['nullable', 'date'],
            'fecha_incorporacion' => ['nullable', 'date'],
            'nivel_escolaridad' => ['nullable', 'string', 'max:100'],
            'estado_civil' => ['nullable', 'string', 'max:100'],
            'ocupacion' => ['nullable', 'string', 'max:150'],
            'grupo_sanguineo' => ['nullable', 'string', 'max:20'],
            'correo_electronico' => ['nullable', 'email', 'max:150'],
            'celular' => ['nullable', 'string', 'max:30'],
            'domicilio' => ['nullable', 'string', 'max:255'],
            'enfermedades' => ['nullable', 'string'],
            'alergias' => ['nullable', 'string'],
            'contacto_emergencia_nombre' => ['nullable', 'string', 'max:150'],
            'contacto_emergencia_numero' => ['nullable', 'string', 'max:30'],
            'foto_perfil' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:10240'],
        ];
    }

    private function syncUsernameFromVolunteer(User $user, array $voluntarioData): void
    {
        $rut = trim((string) ($voluntarioData['rut'] ?? ''));

        if ($rut !== '' && $user->username !== $rut) {
            $user->update(['username' => $rut]);
        }
    }
}
