<?php

namespace Tests\Feature;

use App\Models\Actividad;
use App\Models\Album;
use App\Models\Archivo;
use App\Models\Filial;
use App\Models\GaleriaActividad;
use App\Models\Role;
use App\Models\User;
use App\Models\Voluntario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AlbumAndClimateAuthorizationApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_moderator_can_manage_only_albums_from_their_filial(): void
    {
        $context = $this->createContext();
        Sanctum::actingAs($context['moderator']);

        $this->getJson('/api/albumes')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $context['ownAlbum']->id);

        $this->putJson("/api/albumes/{$context['ownAlbum']->id}", ['nombre' => 'Álbum actualizado'])
            ->assertOk();
        $this->putJson("/api/albumes/{$context['foreignAlbum']->id}", ['nombre' => 'Cambio indebido'])
            ->assertForbidden();
        $this->post("/api/albumes/{$context['foreignAlbum']->id}/fotos", [
            'archivo' => UploadedFile::fake()->image('indebida.jpg'),
        ])->assertForbidden();
        $this->putJson(
            "/api/albumes/{$context['foreignAlbum']->id}/fotos/{$context['foreignPhoto']->id}",
            ['nombre' => 'Cambio indebido']
        )->assertForbidden();
    }

    public function test_volunteer_can_read_only_galleries_and_downloads_from_approved_activities(): void
    {
        $context = $this->createContext();
        Sanctum::actingAs($context['volunteerUser']);

        $this->getJson('/api/albumes')->assertForbidden();
        $this->getJson("/api/actividad/{$context['ownActivity']->id}/galeria")->assertOk();
        $this->getJson("/api/actividad/{$context['foreignActivity']->id}/galeria")->assertForbidden();
        $this->get(
            "/api/albumes/{$context['ownAlbum']->id}/fotos/{$context['ownPhoto']->id}/descargar"
        )->assertOk();
        $this->get(
            "/api/albumes/{$context['foreignAlbum']->id}/fotos/{$context['foreignPhoto']->id}/descargar"
        )->assertForbidden();
        $this->postJson('/api/albumes', [
            'nombre' => 'Álbum no autorizado',
            'actividad_id' => $context['ownActivity']->id,
        ])->assertForbidden();
    }

    public function test_only_a_manager_from_the_activity_filial_can_delete_temporary_climate_images(): void
    {
        $context = $this->createContext();
        Sanctum::actingAs($context['volunteerUser']);

        $this->deleteJson(
            "/api/actividad/{$context['ownActivity']->id}/galeria-temporal/{$context['climatePhoto']->id}"
        )->assertForbidden();
        Storage::disk('public')->assertExists($context['climatePhoto']->ruta);

        Sanctum::actingAs($context['moderator']);
        $this->deleteJson(
            "/api/actividad/{$context['ownActivity']->id}/galeria-temporal/{$context['climatePhoto']->id}"
        )->assertNoContent();
        Storage::disk('public')->assertMissing($context['climatePhoto']->ruta);
    }

    private function createContext(): array
    {
        Storage::fake('public');
        $ownFilial = $this->createFilial('Curico', '07301');
        $foreignFilial = $this->createFilial('Talca', '07101');
        $moderatorRole = Role::query()->where('clave', 'moderador')->firstOrFail();
        $volunteerRole = Role::query()->where('clave', 'voluntario')->firstOrFail();

        $moderator = User::factory()->create();
        $moderator->roles()->attach($moderatorRole);
        $moderatorVolunteer = $this->createVolunteer($moderator, $ownFilial, '10.000.001-9', 'S-1');

        $volunteerUser = User::factory()->create();
        $volunteerUser->roles()->attach($volunteerRole);
        $volunteer = $this->createVolunteer($volunteerUser, $ownFilial, '11.111.111-1', 'V-1');

        $foreignManager = User::factory()->create();
        $foreignManager->roles()->attach($moderatorRole);
        $this->createVolunteer($foreignManager, $foreignFilial, '12.222.222-4', 'F-1');

        $ownActivity = $this->createActivity($ownFilial, $moderator, 'Actividad Curico');
        $foreignActivity = $this->createActivity($foreignFilial, $foreignManager, 'Actividad Talca');
        $ownActivity->inscripciones()->attach($volunteer->id, [
            'estado' => Actividad::INSCRIPCION_APROBADA,
            'horas_asistidas' => 2,
            'registrado_por' => $moderator->id,
            'revisado_por' => $moderator->id,
            'revisado_en' => now(),
        ]);

        $ownAlbum = Album::query()->create([
            'nombre' => 'Álbum Curico',
            'actividad_id' => $ownActivity->id,
            'creado_por' => $moderator->id,
        ]);
        $foreignAlbum = Album::query()->create([
            'nombre' => 'Álbum Talca',
            'actividad_id' => $foreignActivity->id,
            'creado_por' => $foreignManager->id,
        ]);
        $ownPhoto = $this->createAlbumPhoto($ownAlbum, $moderator, 'albumes/curico/foto.webp');
        $foreignPhoto = $this->createAlbumPhoto($foreignAlbum, $foreignManager, 'albumes/talca/foto.webp');

        $climatePath = 'actividades/galeria/clima.webp';
        Storage::disk('public')->put($climatePath, 'clima');
        $climatePhoto = Archivo::query()->create([
            'entidad' => 'actividad',
            'entidad_id' => $ownActivity->id,
            'categoria' => 'clima_documento',
            'ruta' => $climatePath,
            'nombre_original' => 'clima.webp',
            'extension' => 'webp',
            'mime_type' => 'image/webp',
            'tamano' => 5,
            'subido_por' => $moderator->id,
        ]);
        GaleriaActividad::query()->create([
            'actividad_id' => $ownActivity->id,
            'archivo_id' => $climatePhoto->id,
            'titulo' => 'Clima',
            'subido_por' => $moderator->id,
        ]);

        return compact(
            'moderator',
            'moderatorVolunteer',
            'volunteerUser',
            'ownActivity',
            'foreignActivity',
            'ownAlbum',
            'foreignAlbum',
            'ownPhoto',
            'foreignPhoto',
            'climatePhoto'
        );
    }

    private function createFilial(string $name, string $cut): Filial
    {
        return Filial::query()->create([
            'nombre' => "Filial {$name}",
            'cut' => $cut,
            'comite_regional' => 'Maule',
            'direccion' => 'Dirección de prueba',
            'comuna' => $name,
        ]);
    }

    private function createVolunteer(User $user, Filial $filial, string $rut, string $registro): Voluntario
    {
        return Voluntario::query()->create([
            'user_id' => $user->id,
            'filial_id' => $filial->id,
            'registro_filial' => $registro,
            'rut' => $rut,
            'nombres' => 'Persona',
            'apellidos' => $registro,
        ]);
    }

    private function createActivity(Filial $filial, User $creator, string $name): Actividad
    {
        return Actividad::query()->create([
            'filial_id' => $filial->id,
            'creado_por' => $creator->id,
            'nombre' => $name,
            'tipo' => 'Operativa',
            'fecha_inicio' => '2026-08-01',
            'horas_totales' => 4,
        ]);
    }

    private function createAlbumPhoto(Album $album, User $uploader, string $path): Archivo
    {
        Storage::disk('public')->put($path, 'foto');

        return Archivo::query()->create([
            'entidad' => 'album',
            'entidad_id' => $album->id,
            'categoria' => 'foto_album',
            'ruta' => $path,
            'nombre_original' => 'foto.webp',
            'extension' => 'webp',
            'mime_type' => 'image/webp',
            'tamano' => 4,
            'subido_por' => $uploader->id,
        ]);
    }
}
