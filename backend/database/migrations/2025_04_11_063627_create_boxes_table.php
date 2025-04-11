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
         * monto_arriendo
         */
        Schema::create('boxes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('filial_id')->constrained('filials')->onDelete('cascade');
            $table->string('nombre');
            $table->decimal('monto_arriendo', 10, 2);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('boxes');
    }
};
