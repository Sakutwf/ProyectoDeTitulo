<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('hoja_vida_anual', function (Blueprint $table) {
            $table->decimal('asistencia_anual_ajuste_horas', 6, 2)->default(0)->after('asistencia_anual_porcentaje');
        });
    }

    public function down(): void
    {
        Schema::table('hoja_vida_anual', function (Blueprint $table) {
            $table->dropColumn('asistencia_anual_ajuste_horas');
        });
    }
};
