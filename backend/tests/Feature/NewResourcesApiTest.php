<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NewResourcesApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();
    }

    public function test_new_resource_indexes_return_successfully(): void
    {
        $this->getJson('/api/voluntarios')->assertOk()->assertJsonCount(2);
        $this->getJson('/api/hojas-de-vida')->assertOk()->assertJsonCount(2);
        $this->getJson('/api/hojas-anuales')->assertOk()->assertJsonCount(2);
        $this->getJson('/api/antecedentes-voluntarios')->assertOk()->assertJsonCount(2);
    }

    public function test_it_can_create_an_annual_sheet(): void
    {
        $payload = [
            'hoja_de_vida_id' => 1,
            'anio' => 2025,
            'porcentaje_asistencia' => 95.5,
            'cargo' => 'Instructor',
            'lista' => 'Lista A',
            'labor_efectuada' => 'Coordinacion de actividades comunitarias.',
            'observaciones_generales' => 'Buen desempeno anual.',
            'antecedentes' => [
                'cursos' => ['Primeros auxilios', 'Gestion de emergencias'],
                'talleres' => ['Comunicacion efectiva'],
                'seminarios' => ['Seminario regional'],
                'titulos' => ['Monitor comunitario'],
                'premios' => ['Reconocimiento anual'],
            ],
        ];

        $this->postJson('/api/hojas-anuales', $payload)
            ->assertCreated()
            ->assertJsonPath('anio', 2025)
            ->assertJsonPath('cargo', 'Instructor')
            ->assertJsonPath('lista', 'Lista A')
            ->assertJsonPath('labor_efectuada', 'Coordinacion de actividades comunitarias.');

        $this->assertDatabaseHas('hojas_anuales', [
            'hoja_de_vida_id' => 1,
            'anio' => 2025,
            'lista' => 'Lista A',
        ]);

        $this->assertDatabaseHas('antecedentes_voluntarios', [
            'hoja_de_vida_id' => 1,
            'tipo' => 'CURSO',
            'nombre' => 'Primeros auxilios',
            'fecha_inicio' => '2025-01-01',
        ]);

        $this->assertDatabaseHas('antecedentes_voluntarios', [
            'hoja_de_vida_id' => 1,
            'tipo' => 'PREMIO',
            'nombre' => 'Reconocimiento anual',
            'fecha_inicio' => '2025-01-01',
        ]);
    }
}
