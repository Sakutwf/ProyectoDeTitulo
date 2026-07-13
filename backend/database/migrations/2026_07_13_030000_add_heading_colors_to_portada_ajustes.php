<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('portada_ajustes', function (Blueprint $table) {
            $table->string('carrusel_etiqueta_color', 7)->default('#ffffff');
            $table->string('novedades_etiqueta_color', 7)->default('#d72732');
            $table->string('novedades_titulo_color', 7)->default('#011e41');
            $table->string('novedades_descripcion_color', 7)->default('#5f6b7c');
        });
    }

    public function down(): void
    {
        Schema::table('portada_ajustes', fn (Blueprint $table) => $table->dropColumn([
            'carrusel_etiqueta_color', 'novedades_etiqueta_color',
            'novedades_titulo_color', 'novedades_descripcion_color',
        ]));
    }
};
