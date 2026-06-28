<?php

namespace Tests\Feature;

use App\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_login_with_admin_username(): void
    {
        $this->seed();

        $this->postJson('/api/login', [
            'user' => 'admin',
            'id' => 'admin',
        ])
            ->assertOk()
            ->assertJsonPath('user.username', 'admin')
            ->assertJsonFragment(['administrador']);
    }

    public function test_volunteer_can_login_with_rut_as_username(): void
    {
        $this->seed();

        $this->postJson('/api/login', [
            'user' => '22.222.222-2',
            'id' => 'cruzRojaCco26',
        ])
            ->assertOk()
            ->assertJsonPath('user.voluntario.registro_filial', '00001')
            ->assertJsonFragment(['voluntario']);
    }

    public function test_new_volunteer_profile_uses_rut_as_username_when_password_is_omitted(): void
    {
        $this->seed();

        $volunteerRoleId = Role::query()->where('clave', 'voluntario')->value('id');

        $this->postJson('/api/user', [
            'roles' => [$volunteerRoleId],
            'registro_filial' => '99001',
            'filial_id' => 1,
            'rut' => '99.000.001-1',
            'nombres' => 'Nuevo',
            'apellidos' => 'Voluntario',
            'correo_electronico' => 'nuevo.voluntario@cruzroja.local',
            'celular' => '912345678',
        ])->assertCreated();

        $this->postJson('/api/login', [
            'user' => '99.000.001-1',
            'id' => 'cruzRojaCco26',
        ])
            ->assertOk()
            ->assertJsonPath('user.voluntario.registro_filial', '99001')
            ->assertJsonPath('user.username', '99.000.001-1');
    }
}
