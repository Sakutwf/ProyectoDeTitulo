<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PortadaAjuste extends Model
{
    protected $table = 'portada_ajustes';

    protected $fillable = [
        'carrusel_etiqueta', 'novedades_etiqueta',
        'novedades_titulo', 'novedades_descripcion', 'telefono', 'whatsapp', 'correo_contacto',
        'horario_atencion',
        'instagram_url', 'facebook_url', 'directorio', 'enlaces_relacionados',
        'direccion', 'ubicacion_url',
        'carrusel_texto_color',
        'carrusel_etiqueta_color', 'novedades_etiqueta_color',
        'novedades_titulo_color', 'novedades_descripcion_color',
    ];

    protected function casts(): array
    {
        return ['directorio' => 'array', 'enlaces_relacionados' => 'array'];
    }
}
