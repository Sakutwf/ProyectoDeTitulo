<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('actividad_voluntario')
            ->where('estado', 'aprobada')
            ->where(fn ($query) => $query
                ->whereNull('horas_asistidas')
                ->orWhere('horas_asistidas', '<=', 0))
            ->update([
                'estado' => 'pendiente',
                'revisado_por' => null,
                'revisado_en' => null,
                'updated_at' => now(),
            ]);
    }

    public function down(): void
    {
        // Corrección de datos intencionalmente irreversible.
    }
};
