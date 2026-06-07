<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
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
        $this->preparePublicDisk();
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
            'foto_perfil' => $this->fakePngUpload('voluntaria.png'),
        ];

        $this->call('POST', '/api/user', collect($payload)->except('foto_perfil')->all(), [], [
            'foto_perfil' => $payload['foto_perfil'],
        ])
            ->assertCreated()
            ->assertJsonPath('voluntario.n_registro', 'VOL-777')
            ->assertJsonFragment(['slug' => 'voluntario'])
            ->assertJsonFragment(['slug' => 'secretario-directiva']);

        $user = User::where('rut', '55.555.555-5')->firstOrFail()->load('roles.permissions', 'voluntario');

        $this->assertTrue($user->hasRole('voluntario'));
        $this->assertTrue($user->hasRole('secretario-directiva'));
        $this->assertTrue($user->hasPermission('gestionar_voluntarios'));
        $this->assertNotNull($user->voluntario);
        $this->assertNotNull($user->voluntario->foto_perfil);
        Storage::disk('public')->assertExists($user->voluntario->foto_perfil);
    }

    public function test_it_allows_creating_a_volunteer_without_a_profile_photo(): void
    {
        $voluntarioRole = Role::where('slug', 'voluntario')->firstOrFail();

        $payload = [
            'rut' => '66.666.666-6',
            'nombre' => 'Paula Silva',
            'email' => 'paula@example.com',
            'telefono' => '987654321',
            'estado' => 'ACTIVO',
            'password' => 'Secreta123',
            'roles' => [$voluntarioRole->id],
            'fecha_ingreso' => '2024-03-01',
            'n_registro' => 'VOL-888',
            'factor_rh' => '+',
            'grupo_sanguineo' => 'A',
            'fecha_nacimiento' => '2000-01-01',
        ];

        $this->post('/api/user', $payload)
            ->assertCreated()
            ->assertJsonPath('voluntario.foto_perfil_url', null);
    }

    public function test_it_updates_the_volunteer_profile_photo_from_the_history_flow(): void
    {
        $this->preparePublicDisk();
        $user = User::where('rut', '22.222.222-2')->firstOrFail();

        $response = $this->call('POST', "/api/user/{$user->id}/foto-perfil", [], [], [
            'foto_perfil' => $this->fakePngUpload('nueva-foto.png'),
        ]);

        $response
            ->assertOk()
            ->assertJsonPath('voluntario.user_id', $user->id);

        $user->refresh();
        $this->assertNotNull($user->voluntario->foto_perfil);
        Storage::disk('public')->assertExists($user->voluntario->foto_perfil);
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

    private function fakePngUpload(string $name): UploadedFile
    {
        $path = tempnam(sys_get_temp_dir(), 'png');
        file_put_contents($path, base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAusB9sOtTS4AAAAASUVORK5CYII='));

        return new UploadedFile($path, $name, 'image/png', null, true);
    }

    private function preparePublicDisk(): void
    {
        $root = sys_get_temp_dir().DIRECTORY_SEPARATOR.'cruz-roja-public-'.uniqid();
        File::ensureDirectoryExists($root);
        config(['filesystems.disks.public.root' => $root]);
    }
}
