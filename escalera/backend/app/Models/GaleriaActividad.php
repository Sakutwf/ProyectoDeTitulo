<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GaleriaActividad extends Model
{
    protected $table = 'galerias_actividad';

    protected $fillable = [
        'actividad_id',
        'archivo_id',
        'titulo',
        'descripcion',
        'fecha',
        'subido_por',
    ];

    protected $appends = [
        'imagen_url',
    ];

    protected function casts(): array
    {
        return [
            'fecha' => 'date',
        ];
    }

    public function actividad()
    {
        return $this->belongsTo(Actividad::class);
    }

    public function archivo()
    {
        return $this->belongsTo(Archivo::class);
    }

    public function subidoPor()
    {
        return $this->belongsTo(User::class, 'subido_por');
    }

    public function getImagenUrlAttribute(): ?string
    {
        return $this->archivo?->url_publica;
    }
}
