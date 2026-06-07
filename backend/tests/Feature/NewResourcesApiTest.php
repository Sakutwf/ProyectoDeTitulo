<?php

namespace Tests\Feature;

use Illuminate\Http\UploadedFile;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
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
            'cargo' => 'Instructor',
            'lista' => 'Lista A',
            'labor_efectuada' => 'Coordinacion de actividades comunitarias.',
            'observaciones_generales' => 'Buen desempeno anual.',
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
    }

    public function test_it_can_create_a_title_or_award_with_image(): void
    {
        $this->preparePublicDisk();

        $file = $this->fakePngUpload('premio.png');

        $this->call('POST', '/api/antecedentes-voluntarios', [
            'hoja_de_vida_id' => 1,
            'tipo' => 'PREMIO',
            'nombre' => 'Reconocimiento anual',
            'descripcion' => 'Otorgado por desempeno destacado.',
            'fecha_inicio' => '2025-04-15',
        ], [], [
            'archivo' => $file,
        ], [
            'Accept' => 'application/json',
        ])->assertCreated()
            ->assertJsonPath('tipo', 'PREMIO')
            ->assertJsonPath('nombre', 'Reconocimiento anual');

        $antecedente = \App\Models\AntecedenteVoluntario::query()
            ->where('hoja_de_vida_id', 1)
            ->where('tipo', 'PREMIO')
            ->where('nombre', 'Reconocimiento anual')
            ->firstOrFail();

        $this->assertNotNull($antecedente->archivo);
        Storage::disk('public')->assertExists($antecedente->archivo);
    }

    public function test_it_can_create_a_title_or_award_with_pdf(): void
    {
        $this->preparePublicDisk();

        $file = $this->fakePdfUpload('certificado.pdf');

        $this->call('POST', '/api/antecedentes-voluntarios', [
            'hoja_de_vida_id' => 1,
            'tipo' => 'TITULO',
            'nombre' => 'Certificacion de primeros auxilios',
            'fecha_inicio' => '2025-06-05',
        ], [], [
            'archivo' => $file,
        ], [
            'Accept' => 'application/json',
        ])->assertCreated()
            ->assertJsonPath('tipo', 'TITULO')
            ->assertJsonPath('nombre', 'Certificacion de primeros auxilios');

        $antecedente = \App\Models\AntecedenteVoluntario::query()
            ->where('hoja_de_vida_id', 1)
            ->where('tipo', 'TITULO')
            ->where('nombre', 'Certificacion de primeros auxilios')
            ->firstOrFail();

        $this->assertNotNull($antecedente->archivo);
        $this->assertStringEndsWith('.pdf', strtolower($antecedente->archivo));
        Storage::disk('public')->assertExists($antecedente->archivo);
    }

    private function fakePngUpload(string $name): UploadedFile
    {
        $path = tempnam(sys_get_temp_dir(), 'png');
        file_put_contents($path, base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAusB9sOtTS4AAAAASUVORK5CYII='));

        return new UploadedFile($path, $name, 'image/png', null, true);
    }

    private function fakePdfUpload(string $name): UploadedFile
    {
        $path = tempnam(sys_get_temp_dir(), 'pdf');
        file_put_contents($path, "%PDF-1.4\n1 0 obj\n<<>>\nendobj\ntrailer\n<<>>\n%%EOF");

        return new UploadedFile($path, $name, 'application/pdf', null, true);
    }

    private function preparePublicDisk(): void
    {
        $root = sys_get_temp_dir().DIRECTORY_SEPARATOR.'cruz-roja-public-'.uniqid();
        File::ensureDirectoryExists($root);
        config(['filesystems.disks.public.root' => $root]);
    }
}
