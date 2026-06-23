<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CursoVoluntario extends Model
{
    protected $table = 'cursos_voluntario';

    protected $fillable = [
        'hoja_vida_anual_id',
        'nombre_curso',
        'entregado_por',
        'codigo_curso',
    ];

    public function hojaVidaAnual()
    {
        return $this->belongsTo(HojaVidaAnual::class, 'hoja_vida_anual_id');
    }
}
