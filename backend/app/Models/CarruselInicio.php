<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CarruselInicio extends Model
{
    protected $table = 'carrusel_inicio';
    protected $fillable = ['titulo', 'titulo_color', 'bajada', 'bajada_color', 'orden', 'publicada', 'creado_por'];
    protected function casts(): array { return ['publicada' => 'boolean']; }

    public function imagenes()
    {
        return $this->belongsToMany(Archivo::class, 'carrusel_inicio_imagenes')
            ->withPivot('orden', 'posicion_x', 'posicion_y', 'zoom')->orderByPivot('orden');
    }
}
