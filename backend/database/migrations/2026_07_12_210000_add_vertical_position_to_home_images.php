<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('novedades', function (Blueprint $table) {
            $table->unsignedTinyInteger('posicion_y')->default(50)->after('archivo_portada_id');
        });

        Schema::table('carrusel_inicio_imagenes', function (Blueprint $table) {
            $table->unsignedTinyInteger('posicion_y')->default(50)->after('orden');
        });
    }

    public function down(): void
    {
        Schema::table('carrusel_inicio_imagenes', fn (Blueprint $table) => $table->dropColumn('posicion_y'));
        Schema::table('novedades', fn (Blueprint $table) => $table->dropColumn('posicion_y'));
    }
};
