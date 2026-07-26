<?php

namespace Tests\Feature;

use App\Models\Actividad;
use App\Models\Filial;
use App\Models\Role;
use App\Models\User;
use App\Models\Voluntario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class GalleryAndReceiptApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_volunteer_can_upload_photos_only_to_approved_activities(): void
    {
        Storage::fake('public');
        [$approvedActivity, $otherActivity, $user] = $this->createContext();
        Sanctum::actingAs($user);

        $this->post("/api/actividad/{$approvedActivity->id}/galeria", [
            'archivo' => UploadedFile::fake()->image('actividad.jpg'),
            'titulo' => 'Evidencia de actividad',
        ])->assertCreated()->assertJsonPath('titulo', 'Evidencia de actividad');

        $this->post("/api/actividad/{$otherActivity->id}/galeria", [
            'archivo' => UploadedFile::fake()->image('ajena.jpg'),
        ])->assertForbidden();

        $this->assertDatabaseCount('galerias_actividad', 1);
    }

    public function test_volunteer_cannot_submit_or_read_receipts_for_another_volunteer(): void
    {
        Storage::fake('public');
        [$activity, , $user, $volunteer, $otherVolunteer] = $this->createContext();
        $activity->inscripciones()->attach($otherVolunteer->id, [
            'estado' => Actividad::INSCRIPCION_APROBADA,
            'horas_asistidas' => 1,
            'registrado_por' => $user->id,
            'revisado_por' => $user->id,
            'revisado_en' => now(),
        ]);
        Sanctum::actingAs($user);

        $this->post("/api/actividad/{$activity->id}/boletas", [
            'voluntario_id' => $otherVolunteer->id,
            'archivo' => UploadedFile::fake()->create('ajena.pdf', 50, 'application/pdf'),
            'detalle_compra' => 'Compra ajena',
            'monto' => 1500.50,
        ])->assertForbidden();

        $this->post("/api/actividad/{$activity->id}/boletas", [
            'voluntario_id' => $volunteer->id,
            'archivo' => UploadedFile::fake()->create('propia.pdf', 50, 'application/pdf'),
            'detalle_compra' => 'Compra propia',
            'monto' => 2500.75,
        ])->assertCreated()->assertJsonPath('estado', 'solicitado');

        $this->getJson("/api/actividad/{$activity->id}/boletas?voluntario_id={$otherVolunteer->id}")
            ->assertForbidden();
        $this->assertDatabaseCount('boletas_viatico', 1);
    }

    private function createContext(): array
    {
        $filial = Filial::query()->create([
            'nombre' => 'Filial Curico',
            'cut' => '07301',
            'comite_regional' => 'Maule',
            'direccion' => 'Estado 206',
            'comuna' => 'Curico',
        ]);
        $role = Role::query()->where('clave', 'voluntario')->firstOrFail();
        $user = User::factory()->create();
        $user->roles()->attach($role);
        $volunteer = Voluntario::query()->create([
            'user_id' => $user->id,
            'filial_id' => $filial->id,
            'registro_filial' => '00001',
            'rut' => '11.111.111-1',
            'nombres' => 'Voluntaria',
            'apellidos' => 'Propietaria',
        ]);
        $otherUser = User::factory()->create();
        $otherUser->roles()->attach($role);
        $otherVolunteer = Voluntario::query()->create([
            'user_id' => $otherUser->id,
            'filial_id' => $filial->id,
            'registro_filial' => '00002',
            'rut' => '22.222.222-2',
            'nombres' => 'Otra',
            'apellidos' => 'Voluntaria',
        ]);
        $approvedActivity = Actividad::query()->create([
            'filial_id' => $filial->id,
            'creado_por' => $user->id,
            'nombre' => 'Actividad inscrita',
            'tipo' => 'Operativa',
            'fecha_inicio' => '2026-07-30',
            'horas_totales' => 4,
        ]);
        $otherActivity = Actividad::query()->create([
            'filial_id' => $filial->id,
            'creado_por' => $user->id,
            'nombre' => 'Actividad ajena',
            'tipo' => 'Operativa',
            'fecha_inicio' => '2026-08-01',
            'horas_totales' => 4,
        ]);
        $approvedActivity->inscripciones()->attach($volunteer->id, [
            'estado' => Actividad::INSCRIPCION_APROBADA,
            'horas_asistidas' => 2.5,
            'registrado_por' => $user->id,
            'revisado_por' => $user->id,
            'revisado_en' => now(),
        ]);

        return [$approvedActivity, $otherActivity, $user, $volunteer, $otherVolunteer];
    }
}
