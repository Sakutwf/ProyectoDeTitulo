<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HojaVidaAnual extends Model
{
    protected $table = 'hojas_vida_anuales';

    protected $fillable = [
        'voluntario_n_registro',
        'anio',
        'asistencia_anual_horas',
        'asistencia_anual_porcentaje',
        'estuvo_comision_servicio',
        'comision_fecha_inicio',
        'comision_fecha_termino',
        'comision_lugar',
        'comision_actividad',
        'comentarios',
    ];

    protected function casts(): array
    {
        return [
            'anio' => 'integer',
            'asistencia_anual_horas' => 'decimal:2',
            'asistencia_anual_porcentaje' => 'decimal:2',
            'estuvo_comision_servicio' => 'boolean',
            'comision_fecha_inicio' => 'date',
            'comision_fecha_termino' => 'date',
        ];
    }

    public function voluntario()
    {
        return $this->belongsTo(Voluntario::class, 'voluntario_n_registro', 'n_registro');
    }

    public function titulos()
    {
        return $this->hasMany(TituloVoluntario::class, 'hoja_vida_anual_id');
    }

    public function cursos()
    {
        return $this->hasMany(CursoVoluntario::class, 'hoja_vida_anual_id');
    }

    public function sanciones()
    {
        return $this->hasMany(SancionVoluntario::class, 'hoja_vida_anual_id');
    }

    public function reconocimiento()
    {
        return $this->hasOne(ReconocimientoVoluntario::class, 'hoja_vida_anual_id');
    }
}
