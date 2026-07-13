<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('portada_ajustes', function (Blueprint $table) {
            $table->string('horario_atencion', 500)->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('portada_ajustes', fn (Blueprint $table) => $table->dropColumn('horario_atencion'));
    }
};
