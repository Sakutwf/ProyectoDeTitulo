<?php

namespace Tests\Feature;

use App\Models\Filial;
use App\Models\Role;
use App\Models\User;
use App\Models\Voluntario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class UserPrivacyApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_volunteer_can_view_and_update_only_their_own_profile(): void
    {
        Storage::fake('public');
        $filial = Filial::query()->create([
            'nombre' => 'Filial Curico',
            'cut' => '07301',
            'comite_regional' => 'Maule',
            'direccion' => 'Estado 206',
            'comuna' => 'Curico',
        ]);
        $role = Role::query()->where('clave', 'voluntario')->firstOrFail();
        $owner = $this->createVolunteerUser($role, $filial, '11.111.111-1', '00001');
        $other = $this->createVolunteerUser($role, $filial, '22.222.222-2', '00002');
        Sanctum::actingAs($owner);

        $this->getJson("/api/user/{$owner->id}")->assertOk();
        $this->getJson("/api/user/{$other->id}")->assertForbidden();
        $this->post("/api/user/{$other->id}/foto-perfil", [
            'foto_perfil' => UploadedFile::fake()->image('ajena.jpg'),
        ])->assertForbidden();
        $this->post("/api/user/{$owner->id}/foto-perfil", [
            'foto_perfil' => UploadedFile::fake()->image('propia.jpg'),
        ])->assertOk();

        $this->assertDatabaseHas('archivos', [
            'entidad' => 'voluntario',
            'entidad_id' => $owner->voluntario->id,
            'categoria' => 'foto_perfil',
            'subido_por' => $owner->id,
        ]);
    }

    private function createVolunteerUser(Role $role, Filial $filial, string $rut, string $registro): User
    {
        $user = User::factory()->create();
        $user->roles()->attach($role);
        Voluntario::query()->create([
            'user_id' => $user->id,
            'filial_id' => $filial->id,
            'registro_filial' => $registro,
            'rut' => $rut,
            'nombres' => 'Voluntaria',
            'apellidos' => $registro,
            'correo_electronico' => "{$registro}@example.com",
        ]);

        return $user->fresh('voluntario');
    }
}
