<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Novedad extends Model
{
    protected $table = 'novedades';

    protected $fillable = [
        'actividad_id', 'archivo_portada_id', 'posicion_x', 'posicion_y', 'zoom', 'slug', 'titulo', 'resumen',
        'contenido', 'orden', 'ancho', 'campos_visibles', 'personas_ayudadas', 'publicada', 'creado_por',
    ];

    protected function casts(): array
    {
        return ['campos_visibles' => 'array', 'publicada' => 'boolean'];
    }

    public function actividad() { return $this->belongsTo(Actividad::class); }
    public function archivoPortada() { return $this->belongsTo(Archivo::class, 'archivo_portada_id'); }
}
