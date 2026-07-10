<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BoletaViatico extends Model
{
    protected $table = 'boletas_viatico';

    protected $fillable = [
        'actividad_id',
        'voluntario_id',
        'archivo_id',
        'detalle_compra',
        'monto',
        'fecha_compra',
        'estado',
        'observacion_revision',
        'revisado_por',
    ];

    protected $appends = [
        'archivo_url',
    ];

    protected function casts(): array
    {
        return [
            'monto' => 'decimal:2',
            'fecha_compra' => 'date',
        ];
    }

    public function actividad()
    {
        return $this->belongsTo(Actividad::class);
    }

    public function voluntario()
    {
        return $this->belongsTo(Voluntario::class);
    }

    public function archivo()
    {
        return $this->belongsTo(Archivo::class);
    }

    public function revisadoPor()
    {
        return $this->belongsTo(User::class, 'revisado_por');
    }

    public function getArchivoUrlAttribute(): ?string
    {
        return $this->archivo?->url_publica;
    }
}

