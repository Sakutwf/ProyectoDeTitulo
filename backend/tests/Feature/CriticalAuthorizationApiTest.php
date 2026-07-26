<?php

namespace Tests\Feature;

use App\Models\Actividad;
use App\Models\Filial;
use App\Models\Role;
use App\Models\User;
use App\Models\Voluntario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class CriticalAuthorizationApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_volunteer_cannot_use_administrative_user_activity_or_document_endpoints(): void
    {
        [$activity, $volunteerUser] = $this->createContext();
        Sanctum::actingAs($volunteerUser);

        $this->getJson('/api/user')->assertForbidden();

        $this->postJson('/api/actividad', [
            'filial_id' => $activity->filial_id,
            'creado_por' => $volunteerUser->id,
            'nombre' => 'Actividad no autorizada',
            'tipo' => 'Operativa',
            'fecha_inicio' => '2026-08-01',
            'horas_totales' => 2.5,
        ])->assertForbidden();

        $this->postJson("/api/actividad/{$activity->id}/documentos", [
            'tipo_documento' => 'informe_narrativo',
            'titulo' => 'Documento no autorizado',
            'estado' => 'borrador',
        ])->assertForbidden();
    }

    public function test_moderator_can_use_administrative_endpoints(): void
    {
        [$activity, , $moderator] = $this->createContext();
        Sanctum::actingAs($moderator);

        $this->getJson('/api/user')->assertOk();

        $this->postJson('/api/actividad', [
            'filial_id' => $activity->filial_id,
            'creado_por' => $moderator->id,
            'nombre' => 'Actividad autorizada',
            'tipo' => 'Formativa',
            'fecha_inicio' => '2026-08-02',
            'horas_totales' => 3.5,
        ])->assertCreated()->assertJsonPath('horas_totales', '3.50');

        $this->postJson("/api/actividad/{$activity->id}/documentos", [
            'tipo_documento' => 'informe_narrativo',
            'titulo' => 'Informe autorizado',
            'estado' => 'borrador',
        ])->assertCreated();
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
        $volunteerRole = Role::query()->where('clave', 'voluntario')->firstOrFail();
        $moderatorRole = Role::query()->where('clave', 'moderador')->firstOrFail();

        $volunteerUser = User::factory()->create();
        $volunteerUser->roles()->attach($volunteerRole);
        Voluntario::query()->create([
            'user_id' => $volunteerUser->id,
            'filial_id' => $filial->id,
            'registro_filial' => '00001',
            'rut' => '11.111.111-1',
            'nombres' => 'Voluntaria',
            'apellidos' => 'Prueba',
        ]);

        $moderator = User::factory()->create();
        $moderator->roles()->attach($moderatorRole);
        $activity = Actividad::query()->create([
            'filial_id' => $filial->id,
            'creado_por' => $moderator->id,
            'nombre' => 'Actividad base',
            'tipo' => 'Operativa',
            'fecha_inicio' => '2026-07-30',
            'horas_totales' => 4,
        ]);

        return [$activity, $volunteerUser, $moderator];
    }
}
