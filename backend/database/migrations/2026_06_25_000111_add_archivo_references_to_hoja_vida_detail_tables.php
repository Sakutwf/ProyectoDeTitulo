<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('titulos_voluntario', function (Blueprint $table) {
            if (! Schema::hasColumn('titulos_voluntario', 'archivo_id')) {
                $table->foreignId('archivo_id')
                    ->nullable()
                    ->after('hoja_vida_anual_id')
                    ->constrained('archivos')
                    ->nullOnDelete();
            }
        });

        Schema::table('cursos_voluntario', function (Blueprint $table) {
            if (! Schema::hasColumn('cursos_voluntario', 'archivo_id')) {
                $table->foreignId('archivo_id')
                    ->nullable()
                    ->after('hoja_vida_anual_id')
                    ->constrained('archivos')
                    ->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('cursos_voluntario', function (Blueprint $table) {
            if (Schema::hasColumn('cursos_voluntario', 'archivo_id')) {
                $table->dropConstrainedForeignId('archivo_id');
            }
        });

        Schema::table('titulos_voluntario', function (Blueprint $table) {
            if (Schema::hasColumn('titulos_voluntario', 'archivo_id')) {
                $table->dropConstrainedForeignId('archivo_id');
            }
        });
    }
};
