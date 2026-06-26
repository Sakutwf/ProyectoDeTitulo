<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('actividades', function (Blueprint $table) {
            $table->id();
            $table->foreignId('filial_id')->constrained('filiales')->restrictOnDelete();
            $table->foreignId('creado_por')->constrained('users')->restrictOnDelete();
            $table->string('nombre', 200);
            $table->string('tipo', 100);
            $table->text('objetivo')->nullable();
            $table->date('fecha_inicio');
            $table->date('fecha_termino')->nullable();
            $table->time('hora_inicio')->nullable();
            $table->time('hora_termino')->nullable();
            $table->string('lugar', 255)->nullable();
            $table->decimal('horas_totales', 6, 2)->default(0);
            $table->string('colaborador_externo', 200)->nullable();
            $table->timestamps();
        });

        Schema::create('actividad_voluntario', function (Blueprint $table) {
            $table->id();
            $table->foreignId('actividad_id')->constrained('actividades')->cascadeOnDelete();
            $table->foreignId('voluntario_id')->constrained('voluntarios')->cascadeOnDelete();
            $table->decimal('horas_asistidas', 6, 2)->default(0);
            $table->foreignId('registrado_por')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['actividad_id', 'voluntario_id']);
        });

        Schema::create('galerias_actividad', function (Blueprint $table) {
            $table->id();
            $table->foreignId('actividad_id')->constrained('actividades')->cascadeOnDelete();
            $table->string('titulo', 150)->nullable();
            $table->text('descripcion')->nullable();
            $table->date('fecha')->nullable();
            $table->foreignId('subido_por')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('boletas_viatico', function (Blueprint $table) {
            $table->id();
            $table->foreignId('actividad_id')->constrained('actividades')->cascadeOnDelete();
            $table->foreignId('voluntario_id')->constrained('voluntarios')->cascadeOnDelete();
            $table->string('detalle_compra', 255);
            $table->decimal('monto', 10, 2);
            $table->date('fecha_compra')->nullable();
            $table->string('estado', 50)->nullable();
            $table->text('observacion_revision')->nullable();
            $table->foreignId('revisado_por')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('boletas_viatico');
        Schema::dropIfExists('galerias_actividad');
        Schema::dropIfExists('actividad_voluntario');
        Schema::dropIfExists('actividades');
    }
};
