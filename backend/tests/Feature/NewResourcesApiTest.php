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
            'observaciones_generales' => 'Buen desempeno anual.',
        ];

        $this->postJson('/api/hojas-anuales', $payload)
            ->assertCreated()
            ->assertJsonPath('anio', 2025)
            ->assertJsonPath('cargo', 'Instructor');
    }
}
