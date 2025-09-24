<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Titulo extends Model
{
    protected $fillable = [
        'user_id',
        'tipo',
        'nombre',
        'fecha',
        'observaciones'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
