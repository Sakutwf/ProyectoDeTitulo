<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('novedades', function (Blueprint $table) {
            $table->unsignedSmallInteger('zoom')->default(100)->after('posicion_y');
        });

        Schema::table('carrusel_inicio_imagenes', function (Blueprint $table) {
            $table->unsignedSmallInteger('zoom')->default(100)->after('posicion_y');
        });
    }

    public function down(): void
    {
        Schema::table('carrusel_inicio_imagenes', fn (Blueprint $table) => $table->dropColumn('zoom'));
        Schema::table('novedades', fn (Blueprint $table) => $table->dropColumn('zoom'));
    }
};
