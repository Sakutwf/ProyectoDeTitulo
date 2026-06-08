<?php

namespace Tests\Feature;

use App\Models\Actividad;
use App\Models\Evento;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ActivityEnrollmentApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();
    }

    public function test_volunteer_can_enroll_and_unenroll_from_an_active_service_activity(): void
    {
        $user = User::where('rut', '22.222.222-2')->firstOrFail();

        $evento = Evento::create([
            'nombre' => 'Operativo Urbano',
            'fecha_inicio' => '2026-06-20',
            'fecha_termino' => '2026-06-20',
            'descripcion' => 'Operativo abierto a voluntarios.',
            'tipo' => 'SERVICIO',
        ]);

        $actividad = Actividad::create([
            'evento_id' => $evento->id,
            'nombre' => 'Punto de apoyo',
            'tipo' => 'OPERATIVO',
            'N_beneficiarios' => 12,
            'horas_participacion' => 4,
        ]);

        $this->postJson("/api/actividad/{$actividad->id}/voluntarios", [
            'user_id' => $user->id,
        ])->assertOk();

        $this->assertDatabaseHas('actividad_user', [
            'actividad_id' => $actividad->id,
            'user_id' => $user->id,
            'asistio' => 1,
        ]);

        $this->deleteJson("/api/actividad/{$actividad->id}/voluntarios", [
            'user_id' => $user->id,
        ])->assertOk();

        $this->assertDatabaseMissing('actividad_user', [
            'actividad_id' => $actividad->id,
            'user_id' => $user->id,
        ]);
    }
}
