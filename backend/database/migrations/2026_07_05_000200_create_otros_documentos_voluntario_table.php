<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('otros_documentos_voluntario', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hoja_vida_anual_id')->constrained('hoja_vida_anual')->cascadeOnDelete();
            $table->foreignId('archivo_id')->nullable()->constrained('archivos')->nullOnDelete();
            $table->string('nombre_documento', 150);
            $table->string('motivo', 255)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('otros_documentos_voluntario');
    }
};
