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
        /**
         * los datos son:
         * id
         * filial_id
         * codigo
         * nombre
         * cantidad
         * fecha_vencimiento
         */
        Schema::create('materia_primas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('filial_id')->constrained('filials')->onDelete('cascade');
            $table->string('codigo')->unique();
            $table->string('nombre');
            $table->integer('cantidad');
            $table->date('fecha_vencimiento')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('materia_primas');
    }
};
