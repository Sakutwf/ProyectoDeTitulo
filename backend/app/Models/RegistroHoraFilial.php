<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RegistroHoraFilial extends Model
{
    protected $table = 'registros_horas_filial';

    protected $fillable = [
        'user_id',
        'fecha',
        'hora_entrada',
        'hora_salida',
        'horas_totales',
    ];

    protected function casts(): array
    {
        return [
            'fecha' => 'date',
            'horas_totales' => 'decimal:2',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
