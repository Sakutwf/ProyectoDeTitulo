<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Activo extends Model
{

    protected $fillable = [
        'id',
        'filial_id',
        'donacion_id',
        'nombre',
        'cantidad',
        'es_arrendable',
        'created_at',
        'updated_at',
    ];
}
