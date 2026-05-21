<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('antecedentes_voluntarios', function (Blueprint $table) {
            $table->id('id_antecedente');
            $table->foreignId('hoja_de_vida_id')->constrained('hojas_de_vida', 'id_libro')->onDelete('cascade');
            $table->enum('tipo', [
                'CURSO',
                'TALLER',
                'SEMINARIO',
                'CAPACITACION',
                'PREMIO',
                'TITULO',
                'CARGO',
                'OTRO',
            ]);
            $table->string('nombre');
            $table->text('descripcion')->nullable();
            $table->date('fecha_inicio')->nullable();
            $table->date('fecha_termino')->nullable();
            $table->string('duracion')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('antecedentes_voluntarios');
    }
};
