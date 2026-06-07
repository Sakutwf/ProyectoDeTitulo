<?php

namespace Tests\Feature;

use App\Models\Actividad;
use App\Models\Evento;
use App\Models\HojaAnual;
use App\Models\RegistroHoraFilial;
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
            'nombre' => 'Puesto de invierno',
            'tipo' => 'OPERATIVO',
            'N_beneficiarios' => 15,
            'horas_participacion' => 4,
        ]);

        $actividadDos = Actividad::create([
            'evento_id' => $eventoDos->id,
            'nombre' => 'Curso interno',
            'tipo' => 'CURSO',
            'N_beneficiarios' => 20,
            'horas_participacion' => 2,
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

    public function test_filial_hours_are_added_into_the_annual_attendance_percentage(): void
    {
        $user = User::where('rut', '22.222.222-2')->firstOrFail()->load('voluntario.hojaDeVida');
        $hojaDeVidaId = $user->voluntario->hojaDeVida->id_libro;

        $evento = Evento::create([
            'nombre' => 'Operativo Rural',
            'fecha_inicio' => '2026-08-15',
            'fecha_termino' => '2026-08-15',
            'descripcion' => 'Salida comunitaria.',
            'tipo' => 'SERVICIO',
        ]);

        $actividad = Actividad::create([
            'evento_id' => $evento->id,
            'nombre' => 'Atencion en terreno',
            'tipo' => 'OPERATIVO',
            'N_beneficiarios' => 30,
            'horas_participacion' => 4,
        ]);

        $this->putJson("/api/actividad/{$actividad->id}", [
            'planilla_detalle' => [
                [
                    'user_id' => $user->id,
                    'asistio' => false,
                ],
            ],
            'horas_participacion' => 4,
        ])->assertOk();

        RegistroHoraFilial::create([
            'user_id' => $user->id,
            'fecha' => '2026-08-20',
            'hora_entrada' => '09:00',
            'hora_salida' => '13:00',
            'horas_totales' => 4,
        ]);

        app(\App\Services\AttendanceSheetService::class)->syncForUsers([$user->id], [2026]);

        $porcentaje = (float) HojaAnual::where('hoja_de_vida_id', $hojaDeVidaId)
            ->where('anio', 2026)
            ->value('porcentaje_asistencia');

        $this->assertSame(50.0, $porcentaje);
    }
}
