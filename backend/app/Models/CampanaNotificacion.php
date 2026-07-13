<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CampanaNotificacion extends Model
{
    protected $table = 'campanas_notificacion';

    protected $fillable = [
        'tipo', 'asunto_type', 'asunto_id', 'asunto_correo', 'mensaje', 'metadatos',
        'huella', 'estado', 'autorizada_por', 'autorizada_en',
    ];

    protected function casts(): array
    {
        return ['metadatos' => 'array', 'autorizada_en' => 'datetime'];
    }

    public function asunto() { return $this->morphTo(); }
    public function destinatarios() { return $this->hasMany(DestinatarioNotificacion::class, 'campana_id'); }
    public function autorizador() { return $this->belongsTo(User::class, 'autorizada_por'); }
}
