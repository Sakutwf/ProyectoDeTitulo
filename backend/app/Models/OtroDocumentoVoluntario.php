<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OtroDocumentoVoluntario extends Model
{
    protected $table = 'otros_documentos_voluntario';

    protected $fillable = [
        'hoja_vida_anual_id',
        'archivo_id',
        'nombre_documento',
        'motivo',
    ];

    protected $appends = [
        'archivo_url',
        'archivo_nombre',
    ];

    public function hojaVidaAnual()
    {
        return $this->belongsTo(HojaVidaAnual::class, 'hoja_vida_anual_id');
    }

    public function archivo()
    {
        return $this->belongsTo(Archivo::class);
    }

    public function archivosAdjuntos()
    {
        return $this->hasMany(Archivo::class, 'entidad_id')
            ->where('entidad', 'otro_documento_voluntario')
            ->where('categoria', 'respaldo_otro_documento')
            ->orderBy('id');
    }

    public function getArchivoUrlAttribute(): ?string
    {
        return $this->archivo?->url_publica;
    }

    public function getArchivoNombreAttribute(): ?string
    {
        return $this->archivo?->nombre_original;
    }
}
