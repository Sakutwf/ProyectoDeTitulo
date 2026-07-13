<?php

namespace Tests\Feature;

use App\Services\ImageOptimizer;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Laravel\Facades\Image;
use Tests\TestCase;

class ImageOptimizerTest extends TestCase
{
    public function test_it_converts_resizes_and_strips_an_uploaded_image_to_webp(): void
    {
        Storage::fake('public');
        $file = UploadedFile::fake()->image('actividad.jpg', 3000, 2000);

        $result = app(ImageOptimizer::class)->store($file, 'tests/imagenes');

        Storage::disk('public')->assertExists($result['path']);
        $this->assertSame('webp', $result['extension']);
        $this->assertSame('image/webp', $result['mime_type']);
        $this->assertLessThanOrEqual(5 * 1024 * 1024, $result['size']);

        $optimized = Image::read(Storage::disk('public')->path($result['path']));
        $this->assertLessThanOrEqual(ImageOptimizer::MAX_DIMENSION, $optimized->width());
        $this->assertLessThanOrEqual(ImageOptimizer::MAX_DIMENSION, $optimized->height());
        $this->assertSame(1920, $optimized->width());
        $this->assertSame(1280, $optimized->height());
    }
}
