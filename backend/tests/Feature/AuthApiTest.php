<?php

namespace Tests\Feature;

use App\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_login_with_email(): void
    {
        $this->seed();

        $this->postJson('/api/login', [
            'user' => 'admin@cruzroja.local',
            'id' => 'admin',
        ])
            ->assertOk()
            ->assertJsonPath('user.email', 'admin@cruzroja.local')
            ->assertJsonFragment(['administrador']);
    }

    public function test_volunteer_can_login_with_registration_number(): void
    {
        $this->seed();

        $this->postJson('/api/login', [
            'user' => '00001',
            'id' => 'cruzRojaCco26',
        ])
            ->assertOk()
            ->assertJsonPath('user.voluntario.n_registro', '00001')
            ->assertJsonFragment(['voluntario']);
    }

    public function test_new_volunteer_profile_uses_generic_default_password_when_password_is_omitted(): void
    {
        $this->seed();

        $volunteerRoleId = Role::query()->where('clave', 'voluntario')->value('id');

        $this->postJson('/api/user', [
            'email' => 'nuevo.voluntario@cruzroja.local',
            'estado' => true,
            'roles' => [$volunteerRoleId],
            'n_registro' => '99001',
            'filial_id' => 1,
            'rut' => '99.000.001-1',
            'nombres' => 'Nuevo',
            'apellidos' => 'Voluntario',
            'celular' => '912345678',
        ])->assertCreated();

        $this->postJson('/api/login', [
            'user' => '99001',
            'id' => 'cruzRojaCco26',
        ])
            ->assertOk()
            ->assertJsonPath('user.voluntario.n_registro', '99001');
    }
}
