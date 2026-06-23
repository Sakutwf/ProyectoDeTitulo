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
            $table->foreignId('filial_id')->constrained('filiales');
            $table->foreignId('creado_por')->constrained('users');
            $table->string('nombre', 200);
            $table->enum('tipo', ['Operativa', 'Formación', 'En filial', 'Reunion']);
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
            $table->string('voluntario_n_registro', 30);
            $table->decimal('horas_asistidas', 6, 2)->default(0);
            $table->foreignId('registrado_por')->nullable()->constrained('users');
            $table->timestamps();

            $table->foreign('voluntario_n_registro')->references('n_registro')->on('voluntarios')->cascadeOnDelete();
            $table->unique(['actividad_id', 'voluntario_n_registro']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('actividad_voluntario');
        Schema::dropIfExists('actividades');
    }
};
