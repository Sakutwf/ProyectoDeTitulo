<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Hist extends Model
{
    protected $table = 'histactividades';
    protected $fillable = [
        'id',
        'user_id',
        'tipo',
        'nombre',
        'fecha_inicio',
        'asistencia',
        'observaciones',
        'created_at',
        'updated_at'
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
