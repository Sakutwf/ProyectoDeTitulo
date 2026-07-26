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

class DocumentAndHomepageApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_final_document_replaces_previous_drafts_of_the_same_type(): void
    {
        [$activity, $administrator] = $this->createContext();
        Sanctum::actingAs($administrator);

        $this->postJson("/api/actividad/{$activity->id}/documentos", [
            'tipo_documento' => 'informe_narrativo',
            'titulo' => 'Borrador inicial',
            'estado' => 'borrador',
        ])->assertCreated();

        $this->postJson("/api/actividad/{$activity->id}/documentos", [
            'tipo_documento' => 'informe_narrativo',
            'titulo' => 'Informe definitivo',
            'estado' => 'final',
            'contenido' => ['resumen' => 'Resultado final de la actividad.'],
        ])->assertCreated()->assertJsonPath('estado', 'final');

        $this->assertDatabaseMissing('documentos_actividad', ['titulo' => 'Borrador inicial']);
        $this->assertDatabaseHas('documentos_actividad', [
            'actividad_id' => $activity->id,
            'titulo' => 'Informe definitivo',
            'estado' => 'final',
        ]);
    }

    public function test_administrators_and_moderators_can_persist_public_homepage_settings(): void
    {
        [, $administrator, $volunteer, $moderator] = $this->createContext();
        $payload = [
            'textos' => [
                'carrusel_etiqueta' => 'Historias que nos unen',
                'novedades_etiqueta' => 'Actualidad',
                'novedades_titulo' => 'Novedades de Curicó',
                'novedades_descripcion' => 'Información de la filial.',
                'carrusel_texto_color' => '#ffffff',
                'carrusel_etiqueta_color' => '#ffffff',
                'novedades_etiqueta_color' => '#f5333f',
                'novedades_titulo_color' => '#011e41',
                'novedades_descripcion_color' => '#5f6b7c',
                'telefono' => '+56 9 1234 5678',
                'directorio' => [],
                'enlaces_relacionados' => [],
            ],
            'novedades' => [],
            'carrusel' => [],
        ];

        Sanctum::actingAs($volunteer);
        $this->putJson('/api/portada/configuracion', $payload)->assertForbidden();

        Sanctum::actingAs($administrator);
        $this->putJson('/api/portada/configuracion', $payload)
            ->assertOk()
            ->assertJsonPath('textos.telefono', '+56 9 1234 5678');

        Sanctum::actingAs($moderator);
        $this->putJson('/api/portada/configuracion', $payload)->assertOk();

        $this->getJson('/api/portada')
            ->assertOk()
            ->assertJsonPath('textos.novedades_titulo', 'Novedades de Curicó');
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
        $administratorRole = Role::query()->where('clave', 'administrador')->firstOrFail();
        $volunteerRole = Role::query()->where('clave', 'voluntario')->firstOrFail();
        $moderatorRole = Role::query()->where('clave', 'moderador')->firstOrFail();
        $administrator = User::factory()->create();
        $administrator->roles()->attach($administratorRole);
        $volunteer = User::factory()->create();
        $volunteer->roles()->attach($volunteerRole);
        $moderator = User::factory()->create();
        $moderator->roles()->attach($moderatorRole);
        Voluntario::query()->create([
            'user_id' => $moderator->id,
            'filial_id' => $filial->id,
            'registro_filial' => 'MOD-001',
            'rut' => '13.333.333-5',
            'nombres' => 'Persona',
            'apellidos' => 'Moderadora',
        ]);
        $activity = Actividad::query()->create([
            'filial_id' => $filial->id,
            'creado_por' => $administrator->id,
            'nombre' => 'Actividad documentada',
            'tipo' => 'Formativa',
            'fecha_inicio' => '2026-07-30',
            'horas_totales' => 4,
        ]);

        return [$activity, $administrator, $volunteer, $moderator];
    }
}
