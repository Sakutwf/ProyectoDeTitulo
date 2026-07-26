<?php

namespace Tests\Feature;

use App\Models\Actividad;
use App\Models\DocumentoActividad;
use App\Models\Filial;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AdministrativeMutationAuthorizationApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_volunteer_cannot_update_or_delete_administrative_records(): void
    {
        $filial = Filial::query()->create([
            'nombre' => 'Filial Curico',
            'cut' => '07301',
            'comite_regional' => 'Maule',
            'direccion' => 'Estado 206',
            'comuna' => 'Curico',
        ]);
        $volunteerRole = Role::query()->where('clave', 'voluntario')->firstOrFail();
        $administrator = User::factory()->create();
        $volunteer = User::factory()->create();
        $volunteer->roles()->attach($volunteerRole);
        $activity = Actividad::query()->create([
            'filial_id' => $filial->id,
            'creado_por' => $administrator->id,
            'nombre' => 'Actividad protegida',
            'tipo' => 'Operativa',
            'fecha_inicio' => '2026-07-30',
            'horas_totales' => 4,
        ]);
        $document = DocumentoActividad::query()->create([
            'actividad_id' => $activity->id,
            'tipo_documento' => 'informe_narrativo',
            'titulo' => 'Documento protegido',
            'estado' => 'borrador',
            'fecha_documento' => '2026-07-30',
            'generado_por' => $administrator->id,
        ]);
        Sanctum::actingAs($volunteer);

        $this->putJson("/api/user/{$administrator->id}", ['username' => 'cambio-no-autorizado'])
            ->assertForbidden();
        $this->deleteJson("/api/user/{$administrator->id}")->assertForbidden();
        $this->putJson("/api/actividad/{$activity->id}", ['nombre' => 'Cambio no autorizado'])
            ->assertForbidden();
        $this->deleteJson("/api/actividad/{$activity->id}")->assertForbidden();
        $this->putJson("/api/documentos-actividad/{$document->id}", ['titulo' => 'Cambio no autorizado'])
            ->assertForbidden();
        $this->deleteJson("/api/documentos-actividad/{$document->id}")->assertForbidden();

        $this->assertDatabaseHas('users', ['id' => $administrator->id]);
        $this->assertDatabaseHas('actividades', ['id' => $activity->id, 'nombre' => 'Actividad protegida']);
        $this->assertDatabaseHas('documentos_actividad', ['id' => $document->id, 'titulo' => 'Documento protegido']);
    }
}
