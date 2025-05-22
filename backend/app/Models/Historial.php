<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Historial extends Model
{
    protected $fillable = [
        'id',
        'user_id',
        'anio',
        'cargo',
        'taller',
        'curso',
        'seminario',
        'porcentaje_de_asistencia',
        'titulos',
        'premios',
        'observaciones',
        'estudios',
        'created_at',
        'updated_at'
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
