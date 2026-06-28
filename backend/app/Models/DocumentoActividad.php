<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DocumentoActividad extends Model
{
    protected $table = 'documentos_actividad';

    protected $fillable = [
        'actividad_id',
        'tipo_documento',
        'titulo',
        'estado',
        'fecha_documento',
        'datos_contexto',
        'contenido',
        'ruta_pdf',
        'generado_por',
    ];

    protected function casts(): array
    {
        return [
            'fecha_documento' => 'date',
            'datos_contexto' => 'array',
            'contenido' => 'array',
        ];
    }

    public function actividad()
    {
        return $this->belongsTo(Actividad::class);
    }

    public function generador()
    {
        return $this->belongsTo(User::class, 'generado_por');
    }
}
