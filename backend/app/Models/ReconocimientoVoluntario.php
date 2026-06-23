<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReconocimientoVoluntario extends Model
{
    protected $table = 'reconocimientos_voluntario';

    protected $fillable = [
        'hoja_vida_anual_id',
        'servicio_extraordinario',
        'abnegacion',
        'medalla_honor_3',
        'medalla_honor_2',
        'medalla_honor_1',
        'vittorio_cucchini',
        'promesa',
        'juramento',
    ];

    protected function casts(): array
    {
        return [
            'servicio_extraordinario' => 'boolean',
            'abnegacion' => 'boolean',
            'medalla_honor_3' => 'boolean',
            'medalla_honor_2' => 'boolean',
            'medalla_honor_1' => 'boolean',
            'vittorio_cucchini' => 'boolean',
            'promesa' => 'boolean',
            'juramento' => 'boolean',
        ];
    }

    public function hojaVidaAnual()
    {
        return $this->belongsTo(HojaVidaAnual::class, 'hoja_vida_anual_id');
    }
}
