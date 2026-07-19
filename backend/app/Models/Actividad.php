<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Actividad extends Model
{
    public const INSCRIPCION_PENDIENTE = 'pendiente';

    public const INSCRIPCION_APROBADA = 'aprobada';

    public const INSCRIPCION_RECHAZADA = 'rechazada';

    protected $table = 'actividades';

    protected $fillable = [
        'filial_id',
        'creado_por',
        'nombre',
        'tipo',
        'objetivo',
        'fecha_inicio',
        'fecha_termino',
        'hora_inicio',
        'hora_termino',
        'lugar',
        'horas_totales',
        'colaborador_externo',
    ];

    protected function casts(): array
    {
        return [
            'fecha_inicio' => 'date',
            'fecha_termino' => 'date',
            'horas_totales' => 'decimal:2',
        ];
    }

    public function filial()
    {
        return $this->belongsTo(Filial::class);
    }

    public function creador()
    {
        return $this->belongsTo(User::class, 'creado_por');
    }

    public function voluntarios()
    {
        return $this->belongsToMany(
            Voluntario::class,
            'actividad_voluntario',
            'actividad_id',
            'voluntario_id'
        )
            ->wherePivot('estado', self::INSCRIPCION_APROBADA)
            ->withPivot('horas_asistidas', 'estado', 'registrado_por', 'revisado_por', 'revisado_en')
            ->withTimestamps();
    }

    public function inscripciones()
    {
        return $this->belongsToMany(
            Voluntario::class,
            'actividad_voluntario',
            'actividad_id',
            'voluntario_id'
        )
            ->withPivot('horas_asistidas', 'estado', 'registrado_por', 'revisado_por', 'revisado_en')
            ->withTimestamps();
    }

    public function solicitudesPendientes()
    {
        return $this->belongsToMany(
            Voluntario::class,
            'actividad_voluntario',
            'actividad_id',
            'voluntario_id'
        )
            ->wherePivot('estado', self::INSCRIPCION_PENDIENTE)
            ->withPivot('horas_asistidas', 'estado', 'registrado_por', 'revisado_por', 'revisado_en')
            ->withTimestamps();
    }

    public function galeria()
    {
        return $this->hasMany(GaleriaActividad::class, 'actividad_id');
    }

    public function boletasViatico()
    {
        return $this->hasMany(BoletaViatico::class, 'actividad_id');
    }

    public function climas()
    {
        return $this->hasMany(ActividadClima::class, 'actividad_id')
            ->orderBy('orden')
            ->orderBy('id');
    }
    public function documentos()
    {
        return $this->hasMany(DocumentoActividad::class, 'actividad_id');
    }

    public function albumes()
    {
        return $this->hasMany(Album::class, 'actividad_id');
    }
}




