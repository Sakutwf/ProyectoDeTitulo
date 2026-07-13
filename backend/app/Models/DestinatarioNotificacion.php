<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DestinatarioNotificacion extends Model
{
    protected $table = 'destinatarios_notificacion';

    protected $fillable = ['campana_id', 'user_id', 'nombre', 'correo', 'estado', 'error', 'enviado_en'];

    protected function casts(): array
    {
        return ['enviado_en' => 'datetime'];
    }

    public function campana() { return $this->belongsTo(CampanaNotificacion::class, 'campana_id'); }
    public function user() { return $this->belongsTo(User::class); }
}
