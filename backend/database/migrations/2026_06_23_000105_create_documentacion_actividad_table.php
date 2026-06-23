<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('actas_reunion', function (Blueprint $table) {
            $table->id();
            $table->foreignId('actividad_id')->unique()->constrained('actividades')->cascadeOnDelete();
            $table->string('numero_acta', 50);
            $table->date('fecha');
            $table->time('hora_inicio')->nullable();
            $table->time('hora_termino')->nullable();
            $table->string('presidente', 150)->nullable();
            $table->string('vicepresidente', 150)->nullable();
            $table->string('secretario', 150)->nullable();
            $table->string('director_finanzas', 150)->nullable();
            $table->string('director_gestion_riesgo', 150)->nullable();
            $table->string('director_comunicaciones', 150)->nullable();
            $table->string('director_salud', 150)->nullable();
            $table->string('director_juventud', 150)->nullable();
            $table->string('director_bienestar_social', 150)->nullable();
            $table->string('director_desarrollo', 150)->nullable();
            $table->timestamps();
        });

        Schema::create('acuerdos_reunion', function (Blueprint $table) {
            $table->id();
            $table->foreignId('acta_reunion_id')->constrained('actas_reunion')->cascadeOnDelete();
            $table->integer('numero');
            $table->text('descripcion');
            $table->timestamps();
        });

        Schema::create('analisis_contexto', function (Blueprint $table) {
            $table->id();
            $table->foreignId('actividad_id')->unique()->constrained('actividades')->cascadeOnDelete();
            $table->text('conclusion')->nullable();
            $table->longText('contenido_adicional')->nullable();
            $table->string('archivo_original', 255)->nullable();
            $table->timestamps();
        });

        Schema::create('informe_narrativo', function (Blueprint $table) {
            $table->id();
            $table->foreignId('actividad_id')->unique()->constrained('actividades')->cascadeOnDelete();
            $table->date('fecha_informe')->nullable();
            $table->text('objetivo_general')->nullable();
            $table->text('objetivo_especifico')->nullable();
            $table->text('descripcion_general')->nullable();
            $table->text('situaciones_interes')->nullable();
            $table->integer('numero_atenciones_ppaa')->default(0);
            $table->integer('numero_asistencias_movilidad')->default(0);
            $table->integer('numero_votos_asistidos')->default(0);
            $table->integer('numero_traslados')->default(0);
            $table->integer('numero_hombres')->default(0);
            $table->integer('numero_mujeres')->default(0);
            $table->integer('numero_puestos')->default(0);
            $table->text('observaciones_generales')->nullable();
            $table->text('logros')->nullable();
            $table->text('desafios_dificultades')->nullable();
            $table->text('recomendaciones')->nullable();
            $table->text('percepcion')->nullable();
            $table->date('elaboracion_fecha')->nullable();
            $table->string('autorizacion_nombre', 150)->nullable();
            $table->timestamps();
        });

        Schema::create('galeria_actividad', function (Blueprint $table) {
            $table->id();
            $table->foreignId('actividad_id')->constrained('actividades')->cascadeOnDelete();
            $table->foreignId('subido_por')->nullable()->constrained('users');
            $table->string('imagen', 255);
            $table->text('descripcion')->nullable();
            $table->timestamps();
        });

        Schema::create('boletas_viatico', function (Blueprint $table) {
            $table->id();
            $table->foreignId('actividad_id')->constrained('actividades')->cascadeOnDelete();
            $table->string('voluntario_n_registro', 30);
            $table->string('detalle_compra', 255);
            $table->decimal('monto', 10, 2);
            $table->string('foto_boleta', 255);
            $table->foreignId('revisado_por')->nullable()->constrained('users');
            $table->timestamps();

            $table->foreign('voluntario_n_registro')->references('n_registro')->on('voluntarios')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('boletas_viatico');
        Schema::dropIfExists('galeria_actividad');
        Schema::dropIfExists('informe_narrativo');
        Schema::dropIfExists('analisis_contexto');
        Schema::dropIfExists('acuerdos_reunion');
        Schema::dropIfExists('actas_reunion');
    }
};
