<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Voluntario extends Model
{
    protected $primaryKey = 'n_registro';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'n_registro',
        'user_id',
        'filial_id',
        'rut',
        'nombres',
        'apellidos',
        'nacionalidad',
        'fecha_nacimiento',
        'fecha_incorporacion',
        'celular',
        'domicilio',
        'enfermedades',
        'alergias',
        'foto_perfil',
        'contacto_emergencia_nombre',
        'contacto_emergencia_numero',
    ];

    protected $appends = [
        'foto_perfil_url',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function filial()
    {
        return $this->belongsTo(Filial::class);
    }

    public function hojasVidaAnuales()
    {
        return $this->hasMany(HojaVidaAnual::class);
    }

    public function actividades()
    {
        return $this->belongsToMany(
            Actividad::class,
            'actividad_voluntario',
            'voluntario_n_registro',
            'actividad_id',
            'n_registro',
            'id'
        )
            ->withPivot('horas_asistidas', 'registrado_por')
            ->withTimestamps();
    }

    public function getFotoPerfilUrlAttribute(): ?string
    {
        if (! $this->foto_perfil) {
            return null;
        }

        return url('/storage/'.ltrim($this->foto_perfil, '/'));
    }
}
