<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Archivo extends Model
{
    protected $table = 'archivos';

    protected $appends = [
        'url_publica',
    ];

    protected $fillable = [
        'entidad',
        'entidad_id',
        'categoria',
        'ruta',
        'nombre_original',
        'extension',
        'mime_type',
        'tamano',
        'descripcion',
        'subido_por',
    ];

    public function subidoPor()
    {
        return $this->belongsTo(User::class, 'subido_por');
    }

    public function getUrlPublicaAttribute(): ?string
    {
        if (! $this->ruta) {
            return null;
        }

        return url('/storage/'.ltrim($this->ruta, '/'));
    }
}
