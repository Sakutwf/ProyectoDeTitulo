<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SancionVoluntario extends Model
{
    protected $table = 'sanciones_voluntario';

    protected $fillable = [
        'hoja_vida_anual_id',
        'tipo_sancion',
        'fecha',
        'resumen_sancion',
        'apelacion',
        'decision_cig',
        'fecha_apelacion',
    ];

    protected function casts(): array
    {
        return [
            'fecha' => 'date',
            'fecha_apelacion' => 'date',
        ];
    }

    public function hojaVidaAnual()
    {
        return $this->belongsTo(HojaVidaAnual::class, 'hoja_vida_anual_id');
    }
}
