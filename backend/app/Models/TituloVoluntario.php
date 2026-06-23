<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TituloVoluntario extends Model
{
    protected $table = 'titulos_voluntario';

    protected $fillable = [
        'hoja_vida_anual_id',
        'titulo',
        'entregado_por',
        'codigo_titulo',
        'archivo_titulo',
    ];

    public function hojaVidaAnual()
    {
        return $this->belongsTo(HojaVidaAnual::class, 'hoja_vida_anual_id');
    }
}
