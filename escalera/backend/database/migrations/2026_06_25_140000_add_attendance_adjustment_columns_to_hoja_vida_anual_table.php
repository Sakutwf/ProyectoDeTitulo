<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('hoja_vida_anual', function (Blueprint $table) {
            $table->decimal('asistencia_reuniones_filial_ajuste_horas', 6, 2)->default(0)->after('asistencia_anual_ajuste_horas');
            $table->decimal('asistencia_actividades_voluntariado_ajuste_horas', 6, 2)->default(0)->after('asistencia_reuniones_filial_ajuste_horas');
            $table->decimal('asistencia_horas_filial_ajuste_horas', 6, 2)->default(0)->after('asistencia_actividades_voluntariado_ajuste_horas');
            $table->decimal('asistencia_horas_formativas_ajuste_horas', 6, 2)->default(0)->after('asistencia_horas_filial_ajuste_horas');
        });
    }

    public function down(): void
    {
        Schema::table('hoja_vida_anual', function (Blueprint $table) {
            $table->dropColumn([
                'asistencia_reuniones_filial_ajuste_horas',
                'asistencia_actividades_voluntariado_ajuste_horas',
                'asistencia_horas_filial_ajuste_horas',
                'asistencia_horas_formativas_ajuste_horas',
            ]);
        });
    }
};
