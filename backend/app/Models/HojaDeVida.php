<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HojaDeVida extends Model
{
    protected $table = 'hojas_de_vida';

    protected $primaryKey = 'id_libro';

    protected $fillable = [
        'voluntario_id',
        'fecha_creacion',
        'estado',
    ];

    public function voluntario()
    {
        return $this->belongsTo(Voluntario::class);
    }

    public function hojasAnuales()
    {
        return $this->hasMany(HojaAnual::class, 'hoja_de_vida_id', 'id_libro');
    }

    public function antecedentes()
    {
        return $this->hasMany(AntecedenteVoluntario::class, 'hoja_de_vida_id', 'id_libro');
    }
}
