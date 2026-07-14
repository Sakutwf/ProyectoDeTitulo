<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Intervention\Image\Laravel\Facades\Image;

/**
 * Última barrera de optimización de imágenes antes de almacenarlas.
 * Se inyecta en los controladores de álbumes, actividades, portada, usuarios
 * y hojas de vida, además del servicio de aprobación de antecedentes.
 */
class ImageOptimizer
{
    public const MAX_DIMENSION = 1920;

    public const WEBP_QUALITY = 82;

    public function store(
        UploadedFile $file,
        string $directory,
        int $maxOutputBytes = 5 * 1024 * 1024
    ): array {
        $image = Image::read($file->getRealPath());
        $image->scaleDown(width: self::MAX_DIMENSION, height: self::MAX_DIMENSION);

        // La recodificación a WebP elimina EXIF y otros metadatos innecesarios.
        $encoded = $image->toWebp(quality: self::WEBP_QUALITY, strip: true);
        $size = $encoded->size();

        if ($size > $maxOutputBytes) {
            throw ValidationException::withMessages([
                $file->getFilename() => 'La imagen supera el tamaño máximo incluso después de optimizarla.',
            ]);
        }

        $path = trim($directory, '/').'/'.Str::uuid().'.webp';
        Storage::disk('public')->put($path, (string) $encoded);

        return [
            'path' => $path,
            'original_name' => pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME).'.webp',
            'extension' => 'webp',
            'mime_type' => 'image/webp',
            'size' => $size,
        ];
    }
}
