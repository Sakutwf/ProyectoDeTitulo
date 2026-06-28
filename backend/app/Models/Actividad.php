<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Actividad extends Model
{
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
            ->withPivot('horas_asistidas', 'registrado_por')
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

    public function documentos()
    {
        return $this->hasMany(DocumentoActividad::class, 'actividad_id');
    }
}

