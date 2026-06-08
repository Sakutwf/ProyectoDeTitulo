<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();
    }

    public function test_admin_can_login_with_admin_credentials(): void
    {
        $this->postJson('/api/login', [
            'user' => 'admin',
            'id' => 'admin',
        ])
            ->assertOk()
            ->assertJsonPath('user.rut', 'admin')
            ->assertJsonPath('is_admin', true)
            ->assertJsonPath('user.voluntario', null)
            ->assertJsonPath('requires_access_selection', false)
            ->assertJsonFragment(['slug' => 'administrador']);
    }

    public function test_volunteer_can_login_with_registration_number_and_default_password(): void
    {
        $this->postJson('/api/login', [
            'user' => '00001',
            'id' => 'cruzroja26',
        ])
            ->assertOk()
            ->assertJsonPath('user.rut', '22.222.222-2')
            ->assertJsonPath('user.voluntario.n_registro', '00001')
            ->assertJsonPath('requires_access_selection', false)
            ->assertJsonFragment(['slug' => 'voluntario']);
    }

    public function test_admin_volunteer_user_is_prompted_to_choose_access_after_login(): void
    {
        $user = User::where('rut', '22.222.222-2')->firstOrFail();
        $adminRole = Role::where('slug', 'administrador')->firstOrFail();
        $user->roles()->syncWithoutDetaching([$adminRole->id]);

        $this->postJson('/api/login', [
            'user' => '00001',
            'id' => 'cruzroja26',
        ])
            ->assertOk()
            ->assertJsonPath('user.rut', '22.222.222-2')
            ->assertJsonPath('can_access_admin', true)
            ->assertJsonPath('can_access_volunteer', true)
            ->assertJsonPath('requires_access_selection', true);
    }

    public function test_login_rejects_invalid_credentials(): void
    {
        $this->postJson('/api/login', [
            'user' => 'admin',
            'id' => 'incorrecto',
        ])
            ->assertStatus(422)
            ->assertJsonValidationErrors('user');
    }
}
