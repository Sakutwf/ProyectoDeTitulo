<?php

namespace Tests\Feature;

use App\Models\Filial;
use App\Models\HojaVidaAnual;
use App\Models\Role;
use App\Models\SolicitudHojaVida;
use App\Models\User;
use App\Models\Voluntario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class SolicitudHojaVidaApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_volunteer_change_stays_pending_until_an_administrator_approves_it(): void
    {
        [$sheet, $volunteerUser, $administrator] = $this->createContext();
        Sanctum::actingAs($volunteerUser);

        $this->putJson("/api/hoja-vida-anual/{$sheet->id}", [
            'anio' => 2026,
            'titulos' => [[
                'titulo' => 'Primeros Auxilios',
                'entregado_por' => 'Cruz Roja Chilena',
                'codigo_titulo' => 'PA-2026',
            ]],
            'cursos' => [],
            'otros_documentos' => [],
        ])
            ->assertStatus(202)
            ->assertJsonCount(1, 'solicitudes_pendientes')
            ->assertJsonPath('solicitudes_pendientes.0.estado', 'pendiente');

        $this->assertDatabaseCount('titulos_voluntario', 0);
        $request = SolicitudHojaVida::query()->sole();

        Sanctum::actingAs($administrator);
        $this->putJson("/api/solicitudes-hoja-vida/{$request->id}/revisar", [
            'decision' => 'aprobar',
        ])->assertOk()->assertJsonPath('estado', 'aprobada');

        $this->assertDatabaseHas('titulos_voluntario', [
            'hoja_vida_anual_id' => $sheet->id,
            'titulo' => 'Primeros Auxilios',
            'codigo_titulo' => 'PA-2026',
        ]);
    }

    public function test_rejected_change_does_not_modify_the_life_sheet(): void
    {
        [$sheet, $volunteerUser, $administrator] = $this->createContext();
        Sanctum::actingAs($volunteerUser);

        $this->putJson("/api/hoja-vida-anual/{$sheet->id}", [
            'anio' => 2026,
            'titulos' => [],
            'cursos' => [[
                'nombre_curso' => 'Curso solicitado',
                'entregado_por' => 'Institucion externa',
                'codigo_curso' => 'CUR-1',
            ]],
            'otros_documentos' => [],
        ])->assertStatus(202);

        $request = SolicitudHojaVida::query()->sole();
        Sanctum::actingAs($administrator);
        $this->putJson("/api/solicitudes-hoja-vida/{$request->id}/revisar", [
            'decision' => 'rechazar',
            'motivo' => 'Falta respaldo institucional.',
        ])->assertOk()->assertJsonPath('estado', 'rechazada');

        $this->assertDatabaseCount('cursos_voluntario', 0);
        $this->assertDatabaseHas('solicitudes_hoja_vida', [
            'id' => $request->id,
            'estado' => 'rechazada',
            'motivo_revision' => 'Falta respaldo institucional.',
        ]);
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
        $administratorRole = Role::query()->where('clave', 'administrador')->firstOrFail();

        $volunteerUser = User::factory()->create();
        $volunteerUser->roles()->attach($volunteerRole);
        $volunteer = Voluntario::query()->create([
            'user_id' => $volunteerUser->id,
            'filial_id' => $filial->id,
            'registro_filial' => '00001',
            'rut' => '11.111.111-1',
            'nombres' => 'Voluntaria',
            'apellidos' => 'Solicitante',
        ]);
        $sheet = HojaVidaAnual::query()->create([
            'voluntario_id' => $volunteer->id,
            'anio' => 2026,
            'fecha_generacion' => '2026-01-01',
        ]);

        $administrator = User::factory()->create();
        $administrator->roles()->attach($administratorRole);

        return [$sheet, $volunteerUser, $administrator];
    }
}
