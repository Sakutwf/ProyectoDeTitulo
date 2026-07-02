<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ActividadClima extends Model
{
    protected $table = 'actividad_climas';

    protected $fillable = [
        'actividad_id',
        'archivo_id',
        'orden',
        'temperatura_minima',
        'temperatura_maxima',
        'tipo_clima',
    ];

    protected function casts(): array
    {
        return [
            'temperatura_minima' => 'decimal:2',
            'temperatura_maxima' => 'decimal:2',
        ];
    }

    public function actividad()
    {
        return $this->belongsTo(Actividad::class);
    }

    public function archivo()
    {
        return $this->belongsTo(Archivo::class);
    }
}
