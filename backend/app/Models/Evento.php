<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Evento extends Model
{
    protected $fillable = [
        'id',
        'nombre',
        'fecha_inicio',
        'fecha_termino',
        'Descripcion',
        'tipo',
        'created_at',
        'updated_at'
    ];

    public function actividades()
    {
        return $this->hasMany(Actividad::class, 'evento_id');
    }
}
