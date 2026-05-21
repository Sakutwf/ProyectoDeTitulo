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
        Schema::create('hojas_de_vida', function (Blueprint $table) {
            $table->id('id_libro');
            $table->foreignId('voluntario_id')->unique()->constrained('voluntarios')->onDelete('cascade');
            $table->date('fecha_creacion');
            $table->string('estado');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hojas_de_vida');
    }
};
