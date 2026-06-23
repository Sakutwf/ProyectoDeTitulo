<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hojas_vida_anuales', function (Blueprint $table) {
            $table->id();
            $table->string('voluntario_n_registro', 30);
            $table->year('anio');
            $table->decimal('asistencia_anual_horas', 6, 2)->default(0);
            $table->decimal('asistencia_anual_porcentaje', 5, 2)->default(0);
            $table->boolean('estuvo_comision_servicio')->default(false);
            $table->date('comision_fecha_inicio')->nullable();
            $table->date('comision_fecha_termino')->nullable();
            $table->string('comision_lugar', 255)->nullable();
            $table->text('comision_actividad')->nullable();
            $table->text('comentarios')->nullable();
            $table->timestamps();

            $table->foreign('voluntario_n_registro')->references('n_registro')->on('voluntarios')->cascadeOnDelete();
            $table->unique(['voluntario_n_registro', 'anio']);
        });

        Schema::create('titulos_voluntario', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hoja_vida_anual_id')->constrained('hojas_vida_anuales')->cascadeOnDelete();
            $table->string('titulo', 150);
            $table->string('entregado_por', 150)->nullable();
            $table->string('codigo_titulo', 100)->nullable();
            $table->string('archivo_titulo', 255)->nullable();
            $table->timestamps();
        });

        Schema::create('cursos_voluntario', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hoja_vida_anual_id')->constrained('hojas_vida_anuales')->cascadeOnDelete();
            $table->string('nombre_curso', 150);
            $table->string('entregado_por', 150)->nullable();
            $table->string('codigo_curso', 100)->nullable();
            $table->timestamps();
        });

        Schema::create('sanciones_voluntario', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hoja_vida_anual_id')->constrained('hojas_vida_anuales')->cascadeOnDelete();
            $table->string('tipo_sancion', 150);
            $table->date('fecha')->nullable();
            $table->text('resumen_sancion')->nullable();
            $table->text('apelacion')->nullable();
            $table->text('decision_cig')->nullable();
            $table->date('fecha_apelacion')->nullable();
            $table->timestamps();
        });

        Schema::create('reconocimientos_voluntario', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hoja_vida_anual_id')->unique()->constrained('hojas_vida_anuales')->cascadeOnDelete();
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
        Schema::dropIfExists('hojas_vida_anuales');
    }
};
