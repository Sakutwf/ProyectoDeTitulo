<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AntecedenteVoluntario extends Model
{
    public const TIPOS = [
        'CURSO',
        'TALLER',
        'SEMINARIO',
        'CAPACITACION',
        'PREMIO',
        'TITULO',
        'CARGO',
        'OTRO',
    ];

    protected $table = 'antecedentes_voluntarios';

    protected $primaryKey = 'id_antecedente';

    protected $fillable = [
        'hoja_de_vida_id',
        'tipo',
        'nombre',
        'descripcion',
        'fecha_inicio',
        'fecha_termino',
        'duracion',
    ];

    public function hojaDeVida()
    {
        return $this->belongsTo(HojaDeVida::class, 'hoja_de_vida_id', 'id_libro');
    }
}
