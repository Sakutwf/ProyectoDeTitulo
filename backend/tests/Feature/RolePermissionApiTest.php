<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RolePermissionApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();
    }

    public function test_roles_endpoint_includes_permissions(): void
    {
        $this->getJson('/api/role')
            ->assertOk()
            ->assertJsonFragment(['slug' => 'administrador'])
            ->assertJsonFragment(['slug' => 'gestionar_voluntarios'])
            ->assertJsonFragment(['slug' => 'ver_reportes_boletas']);
    }

    public function test_it_creates_a_user_with_multiple_roles_and_volunteer_profile(): void
    {
        $voluntarioRole = Role::where('slug', 'voluntario')->firstOrFail();
        $secretarioRole = Role::where('slug', 'secretario-directiva')->firstOrFail();

        $payload = [
            'rut' => '55.555.555-5',
            'nombre' => 'Camila Perez',
            'email' => 'camila@example.com',
            'telefono' => '987654321',
            'estado' => 'ACTIVO',
            'password' => 'Secreta123',
            'roles' => [$voluntarioRole->id, $secretarioRole->id],
            'fecha_ingreso' => '2024-03-01',
            'n_registro' => 'VOL-777',
            'factor_rh' => '+',
            'grupo_sanguineo' => 'B',
            'fecha_nacimiento' => '1999-08-15',
        ];

        $this->postJson('/api/user', $payload)
            ->assertCreated()
            ->assertJsonPath('voluntario.n_registro', 'VOL-777')
            ->assertJsonFragment(['slug' => 'voluntario'])
            ->assertJsonFragment(['slug' => 'secretario-directiva']);

        $user = User::where('rut', '55.555.555-5')->firstOrFail()->load('roles.permissions', 'voluntario');

        $this->assertTrue($user->hasRole('voluntario'));
        $this->assertTrue($user->hasRole('secretario-directiva'));
        $this->assertTrue($user->hasPermission('gestionar_voluntarios'));
        $this->assertNotNull($user->voluntario);
    }

    public function test_it_keeps_volunteer_profile_archived_when_volunteer_role_is_removed(): void
    {
        $user = User::where('rut', '22.222.222-2')->firstOrFail()->load('roles.permissions', 'voluntario');
        $finanzasRole = Role::where('slug', 'encargada-finanzas')->firstOrFail();

        $payload = [
            'rut' => $user->rut,
            'nombre' => $user->nombre,
            'email' => $user->email,
            'telefono' => $user->telefono,
            'estado' => 'ACTIVO',
            'roles' => [$finanzasRole->id],
        ];

        $this->putJson("/api/user/{$user->id}", $payload)
            ->assertOk()
            ->assertJsonPath('voluntario', null)
            ->assertJsonFragment(['slug' => 'encargada-finanzas']);

        $user->refresh()->load('roles.permissions', 'voluntario');

        $this->assertFalse($user->hasRole('voluntario'));
        $this->assertTrue($user->hasPermission('ver_reportes_boletas'));
        $this->assertDatabaseHas('voluntarios', ['user_id' => $user->id]);
        $this->getJson('/api/voluntarios')
            ->assertOk()
            ->assertJsonMissing(['user_id' => $user->id]);
    }

    public function test_it_restores_access_to_existing_volunteer_history_when_role_is_reassigned(): void
    {
        $user = User::where('rut', '22.222.222-2')->firstOrFail();
        $finanzasRole = Role::where('slug', 'encargada-finanzas')->firstOrFail();
        $voluntarioRole = Role::where('slug', 'voluntario')->firstOrFail();

        $this->putJson("/api/user/{$user->id}", [
            'rut' => $user->rut,
            'nombre' => $user->nombre,
            'email' => $user->email,
            'telefono' => $user->telefono,
            'estado' => 'ACTIVO',
            'roles' => [$finanzasRole->id],
        ])->assertOk();

        $this->putJson("/api/user/{$user->id}", [
            'rut' => $user->rut,
            'nombre' => $user->nombre,
            'email' => $user->email,
            'telefono' => $user->telefono,
            'estado' => 'ACTIVO',
            'roles' => [$finanzasRole->id, $voluntarioRole->id],
        ])->assertOk()
            ->assertJsonPath('voluntario.n_registro', 'VOL-001');
    }
}
