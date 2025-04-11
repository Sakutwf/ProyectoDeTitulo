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
        Schema::create('insumos', function (Blueprint $table) {
            $table->id();
            //referencia a la tabla de eventos nullable
            $table->foreignId('evento_id')->nullable()->constrained('eventos')->onDelete('cascade');
            //referencia a la tabla de donaciones
            $table->foreignId('donacion_id')->nullable()->constrained('donacions')->onDelete('cascade');
            $table->string('nombre');
            $table->string('tipo');
            $table->integer('cantidad');
            $table->date('fecha_vencimiento');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('insumos');
    }
};
