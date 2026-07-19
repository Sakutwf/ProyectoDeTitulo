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

class ActividadInscripcionApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_volunteer_enrollment_stays_pending_without_hours(): void
    {
        [$actividad, $administrator, $volunteerUser, $voluntario] = $this->createContext();
        Sanctum::actingAs($volunteerUser);

        $this->postJson("/api/actividad/{$actividad->id}/voluntarios", [
            'voluntario_id' => $voluntario->id,
            ])
            ->assertOk()
            ->assertJsonPath('inscripciones.0.pivot.estado', Actividad::INSCRIPCION_PENDIENTE)
            ->assertJsonPath('inscripciones.0.pivot.horas_asistidas', 0);

        $this->assertDatabaseHas('actividad_voluntario', [
            'actividad_id' => $actividad->id,
            'voluntario_id' => $voluntario->id,
            'estado' => Actividad::INSCRIPCION_PENDIENTE,
            'horas_asistidas' => 0,
        ]);
        $this->assertCount(0, $actividad->fresh()->voluntarios);
    }

    public function test_administrator_volunteer_self_enrollment_stays_pending_and_is_visible(): void
    {
        [$actividad, $administrator, $volunteerUser, $voluntario] = $this->createContext();
        $volunteerUser->roles()->attach(Role::query()->where('clave', 'administrador')->firstOrFail());
        Sanctum::actingAs($volunteerUser);

        $this->postJson('/api/actividad/'.$actividad->id.'/voluntarios', [
            'voluntario_id' => $voluntario->id,
        ])
            ->assertOk()
            ->assertJsonPath('inscripciones.0.pivot.estado', Actividad::INSCRIPCION_PENDIENTE);

        $this->getJson('/api/actividad')
            ->assertOk()
            ->assertJsonPath('data.0.inscripciones.0.pivot.estado', Actividad::INSCRIPCION_PENDIENTE)
            ->assertJsonPath('data.0.solicitudes_pendientes.0.pivot.estado', Actividad::INSCRIPCION_PENDIENTE);
    }

    public function test_administrator_approves_pending_enrollment_and_assigns_hours(): void
    {
        [$actividad, $administrator, $volunteerUser, $voluntario] = $this->createContext();
        Sanctum::actingAs($volunteerUser);
        $this->postJson("/api/actividad/{$actividad->id}/voluntarios", [
            'voluntario_id' => $voluntario->id,
        ])->assertOk();

        Sanctum::actingAs($administrator);
        $this->putJson("/api/actividad/{$actividad->id}/voluntarios/{$voluntario->id}/solicitud", [
            'decision' => 'aprobar',
            'horas_asistidas' => 4,
        ])->assertOk();

        $this->assertDatabaseHas('actividad_voluntario', [
            'actividad_id' => $actividad->id,
            'voluntario_id' => $voluntario->id,
            'estado' => Actividad::INSCRIPCION_APROBADA,
            'horas_asistidas' => 4,
            'revisado_por' => $administrator->id,
        ]);
        $this->assertSame(4.0, (float) $actividad->fresh()->voluntarios->firstOrFail()->pivot->horas_asistidas);
    }

    public function test_approval_requires_hours_within_activity_total(): void
    {
        [$actividad, $administrator, $volunteerUser, $voluntario] = $this->createContext();
        Sanctum::actingAs($volunteerUser);
        $this->postJson("/api/actividad/{$actividad->id}/voluntarios", [
            'voluntario_id' => $voluntario->id,
        ])->assertOk();

        Sanctum::actingAs($administrator);
        $this->putJson("/api/actividad/{$actividad->id}/voluntarios/{$voluntario->id}/solicitud", [
            'decision' => 'aprobar',
        ])->assertJsonValidationErrors('horas_asistidas');

        $this->putJson("/api/actividad/{$actividad->id}/voluntarios/{$voluntario->id}/solicitud", [
            'decision' => 'aprobar',
            'horas_asistidas' => 9,
        ])->assertJsonValidationErrors('voluntarios_detalle.0.horas_asistidas');
    }

    public function test_volunteer_cannot_review_enrollment_requests(): void
    {
        [$actividad, $administrator, $volunteerUser, $voluntario] = $this->createContext();
        Sanctum::actingAs($volunteerUser);
        $this->postJson("/api/actividad/{$actividad->id}/voluntarios", [
            'voluntario_id' => $voluntario->id,
        ])->assertOk();

        $this->putJson("/api/actividad/{$actividad->id}/voluntarios/{$voluntario->id}/solicitud", [
            'decision' => 'aprobar',
            'horas_asistidas' => 4,
        ])->assertForbidden();
    }

    private function createContext(): array
    {
        $filial = Filial::query()->create([
            'nombre' => 'Filial de prueba',
            'cut' => '07301',
            'comite_regional' => 'Maule',
            'direccion' => 'Estado 206',
            'comuna' => 'Curico',
        ]);
        $adminRole = Role::query()->create(['nombre' => 'Administrador', 'clave' => 'administrador']);
        $volunteerRole = Role::query()->create(['nombre' => 'Voluntario', 'clave' => 'voluntario']);

        $administrator = User::factory()->create();
        $administrator->roles()->attach($adminRole);

        $volunteerUser = User::factory()->create();
        $volunteerUser->roles()->attach($volunteerRole);
        $voluntario = Voluntario::query()->create([
            'user_id' => $volunteerUser->id,
            'filial_id' => $filial->id,
            'registro_filial' => '00001',
            'rut' => '11.111.111-1',
            'nombres' => 'Voluntario',
            'apellidos' => 'Pendiente',
        ]);

        $actividad = Actividad::query()->create([
            'filial_id' => $filial->id,
            'creado_por' => $administrator->id,
            'nombre' => 'Actividad con cupos',
            'tipo' => 'Operativa',
            'fecha_inicio' => now()->addDay()->toDateString(),
            'horas_totales' => 8,
        ]);

        return [$actividad, $administrator, $volunteerUser, $voluntario];
    }
}
