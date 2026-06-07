<?php

namespace App\Models;

use App\Enums\EventoTipo;
use Illuminate\Database\Eloquent\Model;

class Evento extends Model
{
    protected $fillable = [
        'id',
        'nombre',
        'fecha_inicio',
        'fecha_termino',
        'descripcion',
        'tipo',
        'created_at',
        'updated_at'
    ];

    protected function casts(): array
    {
        return [
            'tipo' => EventoTipo::class,
        ];
    }

    public function actividades()
    {
        return $this->hasMany(Actividad::class, 'evento_id');
    }
}
