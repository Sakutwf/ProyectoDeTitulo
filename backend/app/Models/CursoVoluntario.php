<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CursoVoluntario extends Model
{
    protected $table = 'cursos_voluntario';

    protected $fillable = [
        'hoja_vida_anual_id',
        'archivo_id',
        'nombre_curso',
        'entregado_por',
        'codigo_curso',
    ];

    protected $appends = [
        'archivo_url',
        'archivo_nombre',
    ];

    public function hojaVidaAnual()
    {
        return $this->belongsTo(HojaVidaAnual::class, 'hoja_vida_anual_id');
    }

    public function archivo()
    {
        return $this->belongsTo(Archivo::class);
    }

    public function getArchivoUrlAttribute(): ?string
    {
        return $this->archivo?->url_publica;
    }

    public function getArchivoNombreAttribute(): ?string
    {
        return $this->archivo?->nombre_original;
    }
}
