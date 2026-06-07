<?php

namespace Tests\Feature;

use App\Models\Actividad;
use App\Models\Evento;
use App\Models\HojaAnual;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AttendanceSyncApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();
    }

    public function test_assigning_and_updating_attendance_recalculates_the_annual_sheet(): void
    {
        $user = User::where('rut', '22.222.222-2')->firstOrFail()->load('voluntario.hojaDeVida');
        $hojaDeVidaId = $user->voluntario->hojaDeVida->id_libro;

        $eventoUno = Evento::create([
            'nombre' => 'Operativo Invierno',
            'fecha_inicio' => '2026-06-01',
            'fecha_termino' => '2026-06-01',
            'descripcion' => 'Primera salida del anio.',
            'tipo' => 'SERVICIO',
        ]);

        $eventoDos = Evento::create([
            'nombre' => 'Jornada de Formacion',
            'fecha_inicio' => '2026-09-10',
            'fecha_termino' => '2026-09-10',
            'descripcion' => 'Actividad formativa del anio.',
            'tipo' => 'FORMATIVO',
        ]);

        $actividadUno = Actividad::create([
            'evento_id' => $eventoUno->id,
            'tipo' => 'OPERATIVO',
            'N_beneficiarios' => 15,
        ]);

        $actividadDos = Actividad::create([
            'evento_id' => $eventoDos->id,
            'tipo' => 'CURSO',
            'N_beneficiarios' => 20,
        ]);

        $this->putJson("/api/actividad/{$actividadUno->id}", [
            'planilla' => [$user->id],
        ])->assertOk();

        $this->putJson("/api/actividad/{$actividadDos->id}", [
            'planilla' => [$user->id],
        ])->assertOk();

        $porcentajeInicial = (float) HojaAnual::where('hoja_de_vida_id', $hojaDeVidaId)
            ->where('anio', 2026)
            ->value('porcentaje_asistencia');

        $this->assertSame(100.0, $porcentajeInicial);

        $this->putJson("/api/actividad/{$actividadDos->id}", [
            'planilla_detalle' => [
                [
                    'user_id' => $user->id,
                    'asistio' => false,
                ],
            ],
        ])->assertOk();

        $porcentajeActualizado = (float) HojaAnual::where('hoja_de_vida_id', $hojaDeVidaId)
            ->where('anio', 2026)
            ->value('porcentaje_asistencia');

        $this->assertSame(100.0, $porcentajeActualizado);

        $this->putJson("/api/actividad/{$actividadUno->id}", [
            'planilla_detalle' => [
                [
                    'user_id' => $user->id,
                    'asistio' => false,
                ],
            ],
        ])->assertOk();

        $porcentajeServicioAusente = (float) HojaAnual::where('hoja_de_vida_id', $hojaDeVidaId)
            ->where('anio', 2026)
            ->value('porcentaje_asistencia');

        $this->assertSame(0.0, $porcentajeServicioAusente);

        $this->getJson("/api/user/{$user->id}")
            ->assertOk()
            ->assertJsonPath('actividades.0.id', $actividadUno->id)
            ->assertJsonPath('actividades.0.pivot.asistio', 0)
            ->assertJsonPath('actividades.1.id', $actividadDos->id)
            ->assertJsonPath('actividades.1.pivot.asistio', 0)
            ->assertJsonPath('voluntario.foto_perfil_url', null);
    }
}
