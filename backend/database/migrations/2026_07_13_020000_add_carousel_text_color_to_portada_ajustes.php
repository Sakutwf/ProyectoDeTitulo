<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('portada_ajustes', function (Blueprint $table) {
            $table->string('carrusel_texto_color', 7)->default('#ffffff');
        });
    }

    public function down(): void
    {
        Schema::table('portada_ajustes', fn (Blueprint $table) => $table->dropColumn('carrusel_texto_color'));
    }
};
