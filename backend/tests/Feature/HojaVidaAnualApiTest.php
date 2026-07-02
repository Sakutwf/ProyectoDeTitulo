<?php

namespace Tests\Feature;

use App\Models\Archivo;
use App\Models\Filial;
use App\Models\HojaVidaAnual;
use App\Models\User;
use App\Models\Voluntario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class HojaVidaAnualApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_creates_an_annual_record_with_titles_courses_sanctions_and_recognition(): void
    {
        $voluntario = $this->createVolunteer();

        $this->postJson("/api/voluntarios/{$voluntario->id}/hoja-vida-anual", [
            'anio' => 2025,
            'asistencia_anual_horas' => 88,
            'asistencia_anual_porcentaje' => 91.5,
            'estuvo_comision_servicio' => true,
            'comision_fecha_inicio' => '2025-03-01',
            'comision_fecha_termino' => '2025-03-10',
            'comision_lugar' => 'Valparaiso',
            'comision_actividad' => 'Apoyo logistico regional',
            'comentarios' => 'Buen desempeno general.',
            'titulos' => [
                [
                    'titulo' => 'Primeros Auxilios',
                    'entregado_por' => 'Cruz Roja Chilena',
                    'codigo_titulo' => 'PA-2025',
                ],
            ],
            'cursos' => [
                [
                    'nombre_curso' => 'Gestion de emergencias',
                    'entregado_por' => 'ONEMI',
                    'codigo_curso' => 'GE-25',
                ],
            ],
            'sanciones' => [
                [
                    'tipo_sancion' => 'Amonestacion verbal',
                    'fecha' => '2025-05-12',
                    'resumen_sancion' => 'Retraso reiterado.',
                ],
            ],
            'reconocimiento' => [
                'servicio_extraordinario' => true,
                'promesa' => true,
            ],
        ])
            ->assertCreated()
            ->assertJsonPath('anio', 2025)
            ->assertJsonPath('titulos.0.titulo', 'Primeros Auxilios')
            ->assertJsonPath('cursos.0.nombre_curso', 'Gestion de emergencias')
            ->assertJsonPath('sanciones.0.tipo_sancion', 'Amonestacion verbal')
            ->assertJsonPath('reconocimiento.servicio_extraordinario', true)
            ->assertJsonPath('reconocimiento.promesa', true);
    }

    public function test_it_updates_an_existing_annual_record_and_replaces_detail_rows(): void
    {
        $voluntario = $this->createVolunteer();
        $record = HojaVidaAnual::query()->create([
            'voluntario_id' => $voluntario->id,
            'anio' => 2024,
            'asistencia_anual_horas' => 10,
            'asistencia_anual_porcentaje' => 20,
            'fecha_generacion' => '2024-12-31',
        ]);

        $record->titulos()->create([
            'titulo' => 'Titulo anterior',
            'entregado_por' => 'Institucion base',
            'codigo_titulo' => 'ANT-01',
        ]);

        $record->cursos()->create([
            'nombre_curso' => 'Curso anterior',
            'entregado_por' => 'Institucion base',
            'codigo_curso' => 'CUR-01',
        ]);

        $record->sanciones()->create([
            'tipo_sancion' => 'Llamado de atencion',
        ]);

        $this->putJson("/api/hoja-vida-anual/{$record->id}", [
            'anio' => 2024,
            'asistencia_anual_horas' => 95,
            'asistencia_anual_porcentaje' => 97,
            'comentarios' => 'Actualizado desde prueba automatizada.',
            'titulos' => [
                [
                    'titulo' => 'Respuesta en Desastres',
                    'entregado_por' => 'IFRC',
                    'codigo_titulo' => 'RD-01',
                ],
            ],
            'cursos' => [],
            'sanciones' => [],
            'reconocimiento' => [
                'abnegacion' => true,
            ],
        ])
            ->assertOk()
            ->assertJsonPath('asistencia_anual_horas', '95.00')
            ->assertJsonPath('titulos.0.titulo', 'Respuesta en Desastres')
            ->assertJsonCount(0, 'cursos')
            ->assertJsonCount(0, 'sanciones')
            ->assertJsonPath('reconocimiento.abnegacion', true)
            ->assertJsonPath('comentarios', 'Actualizado desde prueba automatizada.');
    }

    public function test_it_stores_title_and_course_supporting_files_in_archivos(): void
    {
        $voluntario = $this->createVolunteer();

        $response = $this->post("/api/voluntarios/{$voluntario->id}/hoja-vida-anual", [
            'anio' => 2026,
            'titulos' => [
                [
                    'titulo' => 'Primeros Auxilios Avanzados',
                    'entregado_por' => 'Cruz Roja Chilena',
                    'codigo_titulo' => 'PAA-2026',
                    'archivo' => UploadedFile::fake()->create('titulo.pdf', 120, 'application/pdf'),
                ],
            ],
            'cursos' => [
                [
                    'nombre_curso' => 'Gestion de Albergues',
                    'entregado_por' => 'Cruz Roja Chilena',
                    'codigo_curso' => 'GA-2026',
                    'archivo' => UploadedFile::fake()->create('curso.png', 180, 'image/png'),
                ],
            ],
            'sanciones' => [],
            'reconocimiento' => [],
        ]);

        $response
            ->assertCreated()
            ->assertJsonPath('titulos.0.archivo_nombre', 'titulo.pdf')
            ->assertJsonPath('cursos.0.archivo_nombre', 'curso.png');

        $this->assertDatabaseCount('archivos', 2);

        Archivo::query()->each(function (Archivo $archivo) {
            Storage::disk('public')->assertExists($archivo->ruta);
            Storage::disk('public')->delete($archivo->ruta);
        });
    }

    private function createVolunteer(): Voluntario
    {
        $user = User::factory()->create();
        $filial = Filial::query()->create([
            'nombre' => 'Filial Curico',
            'cut' => '07301',
            'comite_regional' => 'Maule',
            'direccion' => 'Estado 206',
            'comuna' => 'Curico',
        ]);

        return Voluntario::query()->create([
            'user_id' => $user->id,
            'filial_id' => $filial->id,
            'registro_filial' => '00001',
            'rut' => '11.111.111-1',
            'nombres' => 'Voluntario',
            'apellidos' => 'Prueba',
        ]);
    }
}

