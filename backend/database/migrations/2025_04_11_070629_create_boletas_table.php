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
         * materia_prima_id
         * cantidad
         * monto
         * tipo
         * idSII
         * fecha
         */
        Schema::create('boletas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('materia_prima_id')->constrained('materia_primas')->onDelete('cascade');
            $table->integer('cantidad');
            $table->decimal('monto', 10, 2);
            $table->enum('tipo', ['boleta', 'factura']);
            $table->string('idSII')->nullable();
            $table->date('fecha');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('boletas');
    }
};
