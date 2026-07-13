<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SolicitudHojaVida extends Model
{
    protected $table = 'solicitudes_hoja_vida';

    protected $fillable = [
        'hoja_vida_anual_id', 'voluntario_id', 'tipo_registro', 'accion', 'registro_id',
        'datos', 'archivo_ids_conservados', 'estado', 'solicitada_por', 'revisada_por',
        'motivo_revision', 'revisada_en',
    ];

    protected function casts(): array
    {
        return [
            'datos' => 'array',
            'archivo_ids_conservados' => 'array',
            'revisada_en' => 'datetime',
        ];
    }

    public function hojaVidaAnual() { return $this->belongsTo(HojaVidaAnual::class, 'hoja_vida_anual_id'); }
    public function voluntario() { return $this->belongsTo(Voluntario::class); }
    public function solicitante() { return $this->belongsTo(User::class, 'solicitada_por'); }
    public function revisor() { return $this->belongsTo(User::class, 'revisada_por'); }
    public function archivos()
    {
        return $this->hasMany(Archivo::class, 'entidad_id')
            ->where('entidad', 'solicitud_hoja_vida')
            ->where('categoria', 'respaldo_solicitud_hoja_vida')
            ->orderBy('id');
    }
}
