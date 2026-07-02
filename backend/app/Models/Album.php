<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Album extends Model
{
    protected $appends = [
        'creador_nombre',
    ];
    protected $table = 'albumes';

    protected $fillable = [
        'nombre',
        'descripcion',
        'actividad_id',
        'creado_por',
    ];

    public function actividad()
    {
        return $this->belongsTo(Actividad::class);
    }

    public function creador()
    {
        return $this->belongsTo(User::class, 'creado_por');
    }

    public function fotos()
    {
        return $this->hasMany(Archivo::class, 'entidad_id')
            ->where('entidad', 'album')
            ->where('categoria', 'foto_album');
    }

    public function getCreadorNombreAttribute(): string
    {
        return $this->creador?->name ?? $this->creador?->username ?? 'Administrador';
    }
}
