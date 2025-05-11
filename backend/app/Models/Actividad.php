<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Actividad extends Model
{
    protected $fillable = [
        'id',
        'evento_id',
        'planilla',
        'tipo',
        'N_beneficiarios',
        'created_at',
        'updated_at'
    ];

    public function evento()
    {
        return $this->belongsTo(Evento::class, 'evento_id');
    }

    // Ejemplo de consulta con eager loading:
    // Actividad::with('evento')->get();
}
