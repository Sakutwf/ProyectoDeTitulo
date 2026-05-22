<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Voluntario extends Model
{
    protected $fillable = [
        'user_id',
        'fecha_ingreso',
        'n_registro',
        'factor_rh',
        'grupo_sanguineo',
        'fecha_nacimiento',
        'foto_perfil',
    ];

    protected $appends = [
        'foto_perfil_url',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function hojaDeVida()
    {
        return $this->hasOne(HojaDeVida::class);
    }

    public function getFotoPerfilUrlAttribute(): ?string
    {
        if (! $this->foto_perfil) {
            return null;
        }

        return url('/storage/'.ltrim($this->foto_perfil, '/'));
    }
}
