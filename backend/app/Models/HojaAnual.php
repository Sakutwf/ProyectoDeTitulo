<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HojaAnual extends Model
{
    protected $table = 'hojas_anuales';

    protected $primaryKey = 'id_hoja';

    protected $fillable = [
        'hoja_de_vida_id',
        'anio',
        'porcentaje_asistencia',
        'cargo',
        'lista',
        'labor_efectuada',
        'observaciones_generales',
        'cursos',
        'talleres',
        'seminarios',
        'titulos_premios',
    ];

    public function hojaDeVida()
    {
        return $this->belongsTo(HojaDeVida::class, 'hoja_de_vida_id', 'id_libro');
    }
}
