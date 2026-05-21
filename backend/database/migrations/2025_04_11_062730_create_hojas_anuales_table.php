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
        Schema::create('hojas_anuales', function (Blueprint $table) {
            $table->id('id_hoja');
            $table->foreignId('hoja_de_vida_id')->constrained('hojas_de_vida', 'id_libro')->onDelete('cascade');
            $table->year('anio');
            $table->decimal('porcentaje_asistencia', 5, 2)->nullable();
            $table->string('cargo')->nullable();
            $table->text('observaciones_generales')->nullable();
            $table->timestamps();

            $table->unique(['hoja_de_vida_id', 'anio']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hojas_anuales');
    }
};
