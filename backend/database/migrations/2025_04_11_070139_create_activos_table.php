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
         * nombre
         * cantidad
         * es_arrendable
         */
        Schema::create('activos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('filial_id')->constrained('filials')->onDelete('cascade');
            $table->foreignId('donacion_id')->constrained('donacions')->onDelete('cascade');
            $table->string('nombre');
            $table->integer('cantidad');
            $table->boolean('es_arrendable')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('activos');
    }
};
