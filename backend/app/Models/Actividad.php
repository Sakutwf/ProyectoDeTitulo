<?php

namespace App\Models;

use App\Enums\ActividadTipo;
use Illuminate\Database\Eloquent\Model;

class Actividad extends Model
{
    protected $fillable = [
        'id',
        'evento_id',
        'nombre',
        'tipo',
        'N_beneficiarios',
        'created_at',
        'updated_at'
    ];

    protected function casts(): array
    {
        return [
            'tipo' => ActividadTipo::class,
        ];
    }

    public function evento()
    {
        return $this->belongsTo(Evento::class, 'evento_id');
    }

    public function users()
    {
        return $this->belongsToMany(User::class)
            ->withPivot('asistio')
            ->withTimestamps();
    }
}
