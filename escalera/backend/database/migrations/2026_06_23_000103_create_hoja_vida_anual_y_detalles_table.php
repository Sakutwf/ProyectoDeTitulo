<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hoja_vida_anual', function (Blueprint $table) {
            $table->id();
            $table->foreignId('voluntario_id')->constrained('voluntarios')->cascadeOnDelete();
            $table->year('anio');
            $table->decimal('asistencia_anual_horas', 6, 2)->default(0);
            $table->decimal('asistencia_anual_porcentaje', 5, 2)->default(0);
            $table->boolean('estuvo_comision_servicio')->default(false);
            $table->date('comision_fecha_inicio')->nullable();
            $table->date('comision_fecha_termino')->nullable();
            $table->string('comision_lugar', 255)->nullable();
            $table->text('comision_actividad')->nullable();
            $table->text('comentarios')->nullable();
            $table->foreignId('generada_por')->nullable()->constrained('users')->nullOnDelete();
            $table->date('fecha_generacion')->nullable();
            $table->string('ruta_pdf', 255)->nullable();
            $table->timestamps();

            $table->unique(['voluntario_id', 'anio']);
        });

        Schema::create('titulos_voluntario', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hoja_vida_anual_id')->constrained('hoja_vida_anual')->cascadeOnDelete();
            $table->string('titulo', 150);
            $table->string('entregado_por', 150)->nullable();
            $table->string('codigo_titulo', 100)->nullable();
            $table->timestamps();
        });

        Schema::create('cursos_voluntario', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hoja_vida_anual_id')->constrained('hoja_vida_anual')->cascadeOnDelete();
            $table->string('nombre_curso', 150);
            $table->string('entregado_por', 150)->nullable();
            $table->string('codigo_curso', 100)->nullable();
            $table->timestamps();
        });

        Schema::create('sanciones_voluntario', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hoja_vida_anual_id')->constrained('hoja_vida_anual')->cascadeOnDelete();
            $table->string('tipo_sancion', 150);
            $table->date('fecha')->nullable();
            $table->text('resumen_sancion')->nullable();
            $table->text('apelacion')->nullable();
            $table->date('fecha_apelacion')->nullable();
            $table->text('decision_cig')->nullable();
            $table->timestamps();
        });

        Schema::create('reconocimientos_voluntario', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hoja_vida_anual_id')->unique()->constrained('hoja_vida_anual')->cascadeOnDelete();
            $table->boolean('servicio_extraordinario')->default(false);
            $table->boolean('abnegacion')->default(false);
            $table->boolean('medalla_honor_3')->default(false);
            $table->boolean('medalla_honor_2')->default(false);
            $table->boolean('medalla_honor_1')->default(false);
            $table->boolean('vittorio_cucchini')->default(false);
            $table->boolean('promesa')->default(false);
            $table->boolean('juramento')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reconocimientos_voluntario');
        Schema::dropIfExists('sanciones_voluntario');
        Schema::dropIfExists('cursos_voluntario');
        Schema::dropIfExists('titulos_voluntario');
        Schema::dropIfExists('hoja_vida_anual');
    }
};
