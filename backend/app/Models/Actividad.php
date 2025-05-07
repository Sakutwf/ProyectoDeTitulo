<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Actividad extends Model
{
    protected $fillable = [
        'id',
        'nombre',
        'tipo',
        'fecha_inicio',
        'fecha_termino',
        'Descripcion',
        'numero_beneficiarios',
        'created_at',
        'updated_at'
    ];
}
