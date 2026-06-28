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
            $table->string('tipo_documento', 60);
            $table->string('titulo', 200);
            $table->string('estado', 30)->default('borrador');
            $table->date('fecha_documento')->nullable();
            $table->json('datos_contexto')->nullable();
            $table->json('contenido')->nullable();
            $table->string('ruta_pdf')->nullable();
            $table->foreignId('generado_por')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['actividad_id', 'tipo_documento']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('documentos_actividad');
    }
};
