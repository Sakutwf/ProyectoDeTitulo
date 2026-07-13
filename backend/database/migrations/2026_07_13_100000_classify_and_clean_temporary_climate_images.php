<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('archivos')
            ->where('entidad', 'actividad')
            ->where('categoria', 'galeria_actividad')
            ->where('nombre_original', 'like', 'clima-%')
            ->update(['categoria' => 'clima_documento']);

        $usedIds = DB::table('actividad_climas')->whereNotNull('archivo_id')->pluck('archivo_id');
        $orphans = DB::table('archivos')
            ->where('entidad', 'actividad')
            ->where('categoria', 'clima_documento')
            ->when($usedIds->isNotEmpty(), fn ($query) => $query->whereNotIn('id', $usedIds))
            ->get(['id', 'ruta']);

        foreach ($orphans as $orphan) {
            DB::table('galerias_actividad')->where('archivo_id', $orphan->id)->delete();
            if ($orphan->ruta) {
                Storage::disk('public')->delete($orphan->ruta);
            }
            DB::table('archivos')->where('id', $orphan->id)->delete();
        }
    }

    public function down(): void
    {
        DB::table('archivos')
            ->where('entidad', 'actividad')
            ->where('categoria', 'clima_documento')
            ->update(['categoria' => 'galeria_actividad']);
    }
};
