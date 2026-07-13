<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('portada_ajustes', function (Blueprint $table) {
            $table->id();
            $table->string('carrusel_etiqueta', 120)->default('Historias que nos unen');
            $table->string('novedades_etiqueta', 120)->default('Actualidad de nuestra comunidad');
            $table->string('novedades_titulo', 180)->default('Novedades de Cruz Roja');
            $table->string('novedades_descripcion', 300)->default('Conoce las actividades y el impacto de nuestros voluntarios.');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('portada_ajustes');
    }
};
