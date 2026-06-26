<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HojaVidaAnualAjusteHora extends Model
{
    public const TYPE_REUNIONES_FILIAL = 'reuniones_filial';
    public const TYPE_ACTIVIDADES_VOLUNTARIADO = 'actividades_voluntariado';
    public const TYPE_HORAS_FILIAL = 'horas_filial';
    public const TYPE_HORAS_FORMATIVAS = 'horas_formativas';

    protected $table = 'hoja_vida_anual_ajustes_horas';

    protected $fillable = [
        'hoja_vida_anual_id',
        'tipo_actividad',
        'horas_ajuste',
        'motivo',
    ];

    protected function casts(): array
    {
        return [
            'horas_ajuste' => 'decimal:2',
        ];
    }

    public static function allowedTypes(): array
    {
        return [
            self::TYPE_REUNIONES_FILIAL,
            self::TYPE_ACTIVIDADES_VOLUNTARIADO,
            self::TYPE_HORAS_FILIAL,
            self::TYPE_HORAS_FORMATIVAS,
        ];
    }

    public function hojaVidaAnual()
    {
        return $this->belongsTo(HojaVidaAnual::class, 'hoja_vida_anual_id');
    }
}
