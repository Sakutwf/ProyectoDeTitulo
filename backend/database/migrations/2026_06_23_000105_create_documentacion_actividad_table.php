<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('documentos_actividad', function (Blueprint $table) {
            $table->id();
            $table->foreignId('actividad_id')->constrained('actividades')->cascadeOnDelete();
            $table->string('tipo_documento', 100);
            $table->foreignId('generado_por')->nullable()->constrained('users')->nullOnDelete();
            $table->date('fecha_generacion')->nullable();
            $table->string('ruta_pdf', 255)->nullable();
            $table->timestamps();
        });

        Schema::create('analisis_contexto', function (Blueprint $table) {
            $table->id();
            $table->foreignId('actividad_id')->unique()->constrained('actividades')->cascadeOnDelete();
            $table->foreignId('documento_actividad_id')->unique()->constrained('documentos_actividad')->cascadeOnDelete();
            $table->string('numero_documento', 50)->nullable();
            $table->text('objetivo')->nullable();
            $table->text('descripcion_evento')->nullable();
            $table->text('plan_traslado')->nullable();
            $table->text('coordinacion_emergencia')->nullable();
            $table->text('centros_salud_cercanos')->nullable();
            $table->text('conclusion')->nullable();
            $table->foreignId('elaborado_por')->nullable()->constrained('users')->nullOnDelete();
            $table->date('fecha_elaboracion')->nullable();
            $table->timestamps();
        });

        Schema::create('clima_actividad', function (Blueprint $table) {
            $table->id();
            $table->foreignId('analisis_contexto_id')->constrained('analisis_contexto')->cascadeOnDelete();
            $table->date('fecha');
            $table->decimal('temperatura_minima', 5, 2)->nullable();
            $table->decimal('temperatura_maxima', 5, 2)->nullable();
            $table->string('descripcion', 255)->nullable();
            $table->timestamps();
        });

        Schema::create('riesgos_actividad', function (Blueprint $table) {
            $table->id();
            $table->foreignId('analisis_contexto_id')->constrained('analisis_contexto')->cascadeOnDelete();
            $table->string('nombre_riesgo', 150);
            $table->text('descripcion')->nullable();
            $table->string('probabilidad', 50)->nullable();
            $table->string('impacto', 50)->nullable();
            $table->text('medidas_mitigacion')->nullable();
            $table->timestamps();
        });

        Schema::create('informe_narrativo', function (Blueprint $table) {
            $table->id();
            $table->foreignId('actividad_id')->unique()->constrained('actividades')->cascadeOnDelete();
            $table->foreignId('documento_actividad_id')->unique()->constrained('documentos_actividad')->cascadeOnDelete();
            $table->date('fecha_informe')->nullable();
            $table->text('objetivo_general')->nullable();
            $table->text('objetivo_especifico')->nullable();
            $table->text('descripcion_general')->nullable();
            $table->text('situaciones_interes')->nullable();
            $table->unsignedInteger('numero_atenciones_ppaa')->default(0);
            $table->unsignedInteger('numero_asistencias_movilidad')->default(0);
            $table->unsignedInteger('numero_votos_asistidos')->default(0);
            $table->unsignedInteger('numero_traslados')->default(0);
            $table->unsignedInteger('numero_hombres')->default(0);
            $table->unsignedInteger('numero_mujeres')->default(0);
            $table->unsignedInteger('numero_puestos')->default(0);
            $table->unsignedInteger('numero_voluntarios')->default(0);
            $table->unsignedInteger('numero_coordinadores')->default(0);
            $table->unsignedInteger('numero_staff_medico')->default(0);
            $table->unsignedInteger('numero_psicologos')->default(0);
            $table->unsignedInteger('numero_enfermeria')->default(0);
            $table->unsignedInteger('numero_tens')->default(0);
            $table->unsignedInteger('numero_logisticos')->default(0);
            $table->text('observaciones_generales')->nullable();
            $table->text('logros')->nullable();
            $table->text('desafios_dificultades')->nullable();
            $table->text('recomendaciones')->nullable();
            $table->text('percepcion')->nullable();
            $table->string('autorizacion_nombre', 150)->nullable();
            $table->foreignId('elaborado_por')->nullable()->constrained('users')->nullOnDelete();
            $table->date('fecha_elaboracion')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('informe_narrativo');
        Schema::dropIfExists('riesgos_actividad');
        Schema::dropIfExists('clima_actividad');
        Schema::dropIfExists('analisis_contexto');
        Schema::dropIfExists('documentos_actividad');
    }
};
