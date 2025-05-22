<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Premio extends Model
{
    protected $fillable = [
        'user_id',
        'tipo',
        'nombre',
        'fecha',
        'porcentaje_asistencia',
        'observaciones'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
