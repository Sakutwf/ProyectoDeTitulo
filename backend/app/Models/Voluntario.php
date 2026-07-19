<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Voluntario extends Model
{
    public const PROFILE_PHOTO_CATEGORY = 'foto_perfil';

    public const ENTITY_TYPE = 'voluntario';

    protected $fillable = [
        'user_id',
        'filial_id',
        'registro_filial',
        'rut',
        'nombres',
        'apellidos',
        'nacionalidad',
        'fecha_nacimiento',
        'fecha_incorporacion',
        'nivel_escolaridad',
        'estado_civil',
        'ocupacion',
        'grupo_sanguineo',
        'correo_electronico',
        'celular',
        'domicilio',
        'enfermedades',
        'alergias',
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

    public function hojaVidaAnual()
    {
        return $this->hasMany(HojaVidaAnual::class);
    }

    public function archivoFotoPerfil()
    {
        return $this->hasOne(Archivo::class, 'entidad_id')
            ->where('entidad', self::ENTITY_TYPE)
            ->where('categoria', self::PROFILE_PHOTO_CATEGORY);
    }

    public function actividades()
    {
        return $this->belongsToMany(
            Actividad::class,
            'actividad_voluntario',
            'voluntario_id',
            'actividad_id'
        )
            ->wherePivot('estado', Actividad::INSCRIPCION_APROBADA)
            ->withPivot('horas_asistidas', 'estado', 'registrado_por', 'revisado_por', 'revisado_en')
            ->withTimestamps();
    }

    public function boletasViatico()
    {
        return $this->hasMany(BoletaViatico::class);
    }

    public function getFotoPerfilUrlAttribute(): ?string
    {
        $archivo = $this->relationLoaded('archivoFotoPerfil')
            ? $this->getRelation('archivoFotoPerfil')
            : $this->archivoFotoPerfil()->first();

        if (! $archivo?->ruta) {
            return null;
        }

        return url('/storage/'.ltrim($archivo->ruta, '/'));
    }
}
