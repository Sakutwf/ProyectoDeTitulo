<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class RoleArchitectureApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_seed_defines_only_the_three_supported_roles(): void
    {
        $this->seed();

        $this->assertEqualsCanonicalizing(
            ['administrador', 'moderador', 'voluntario'],
            Role::query()->pluck('clave')->all()
        );
    }

    public function test_administrator_is_created_without_a_volunteer_profile(): void
    {
        $this->seed();
        $administrator = User::query()->where('username', 'admin')->firstOrFail();
        $administratorRoleId = Role::query()->where('clave', 'administrador')->value('id');
        Sanctum::actingAs($administrator);

        $response = $this->postJson('/api/user', [
            'username' => 'administracion.general',
            'correo_notificaciones' => 'administracion@example.test',
            'password' => 'segura123',
            'roles' => [$administratorRoleId],
        ])->assertCreated();

        $created = User::query()->findOrFail($response->json('id'));

        $this->assertNull($created->voluntario);
        $this->assertSame(['administrador'], $created->roles()->pluck('clave')->all());
    }

    public function test_current_cargo_promotes_a_volunteer_and_removing_it_restores_the_volunteer_role(): void
    {
        $this->seed();
        $administrator = User::query()->where('username', 'admin')->firstOrFail();
        $volunteer = User::query()->where('username', '22.222.222-2')->firstOrFail();
        $volunteerProfile = $volunteer->voluntario()->firstOrFail();
        Sanctum::actingAs($administrator);

        $response = $this->postJson("/api/voluntarios/{$volunteerProfile->id}/hoja-vida-anual", [
            'anio' => (int) now()->year,
            'cargo_clave' => 'gobernanza_secretario',
        ])->assertCreated();

        $this->assertSame(['moderador'], $volunteer->fresh()->roles()->pluck('clave')->all());

        $this->postJson("/api/hoja-vida-anual/{$response->json('id')}", [
            '_method' => 'PUT',
            'anio' => (int) now()->year,
            'cargo_clave' => null,
        ])->assertOk();

        $this->assertSame(['voluntario'], $volunteer->fresh()->roles()->pluck('clave')->all());
    }

    public function test_moderator_login_offers_administrator_and_volunteer_experiences(): void
    {
        $this->seed();
        $administrator = User::query()->where('username', 'admin')->firstOrFail();
        $volunteer = User::query()->where('username', '22.222.222-2')->firstOrFail();
        $volunteerProfile = $volunteer->voluntario()->firstOrFail();
        Sanctum::actingAs($administrator);

        $this->postJson("/api/voluntarios/{$volunteerProfile->id}/hoja-vida-anual", [
            'anio' => (int) now()->year,
            'cargo_clave' => 'directorio_director_salud',
        ])->assertCreated();

        $this->postJson('/api/login', [
            'user' => '22.222.222-2',
            'id' => 'cruzRojaCco26',
        ])
            ->assertOk()
            ->assertJsonPath('roles.0', 'moderador')
            ->assertJsonPath('can_access_admin', true)
            ->assertJsonPath('can_access_volunteer', true)
            ->assertJsonPath('requires_access_selection', true);
    }
}
