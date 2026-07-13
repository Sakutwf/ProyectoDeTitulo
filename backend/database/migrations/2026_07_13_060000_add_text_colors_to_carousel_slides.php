<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('carrusel_inicio', function (Blueprint $table) {
            $table->string('titulo_color', 7)->default('#ffffff')->after('titulo');
            $table->string('bajada_color', 7)->default('#ffffff')->after('bajada');
        });
    }

    public function down(): void
    {
        Schema::table('carrusel_inicio', fn (Blueprint $table) => $table->dropColumn(['titulo_color', 'bajada_color']));
    }
};
