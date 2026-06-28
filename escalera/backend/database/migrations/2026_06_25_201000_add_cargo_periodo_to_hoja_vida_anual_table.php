<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('hoja_vida_anual', function (Blueprint $table) {
            $table->string('cargo_clave', 120)->nullable()->after('anio');
            $table->string('cargo_nombre', 120)->nullable()->after('cargo_clave');
            $table->string('cargo_grupo', 100)->nullable()->after('cargo_nombre');
            $table->string('cargo_direccion', 100)->nullable()->after('cargo_grupo');
        });
    }

    public function down(): void
    {
        Schema::table('hoja_vida_anual', function (Blueprint $table) {
            $table->dropColumn(['cargo_clave', 'cargo_nombre', 'cargo_grupo', 'cargo_direccion']);
        });
    }
};