<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AntecedenteVoluntario extends Model
{
    public const TIPOS = [
        'CURSO',
        'TALLER',
        'SEMINARIO',
        'CAPACITACION',
        'PREMIO',
        'TITULO',
        'CARGO',
        'OTRO',
    ];

    public const TIPOS_LOGRO = [
        'PREMIO',
        'TITULO',
    ];

    protected $table = 'antecedentes_voluntarios';

    protected $primaryKey = 'id_antecedente';

    protected $appends = [
        'archivo_url',
    ];

    protected $fillable = [
        'hoja_de_vida_id',
        'tipo',
        'nombre',
        'descripcion',
        'fecha_inicio',
        'fecha_termino',
        'duracion',
        'archivo',
    ];

    public function hojaDeVida()
    {
        return $this->belongsTo(HojaDeVida::class, 'hoja_de_vida_id', 'id_libro');
    }

    public function getArchivoUrlAttribute(): ?string
    {
        if (! $this->archivo) {
            return null;
        }

        return url('/storage/'.ltrim($this->archivo, '/'));
    }
}
