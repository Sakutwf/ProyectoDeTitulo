<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Filial extends Model
{
    protected $table = 'filiales';

    protected $fillable = [
        'nombre',
        'comite_regional',
        'direccion',
        'comuna',
    ];

    public function voluntarios()
    {
        return $this->hasMany(Voluntario::class);
    }

    public function actividades()
    {
        return $this->hasMany(Actividad::class);
    }
}
