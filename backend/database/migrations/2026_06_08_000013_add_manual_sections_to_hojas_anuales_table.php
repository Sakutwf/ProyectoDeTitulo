<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('hojas_anuales', function (Blueprint $table) {
            $table->text('cursos')->nullable()->after('observaciones_generales');
            $table->text('talleres')->nullable()->after('cursos');
            $table->text('seminarios')->nullable()->after('talleres');
            $table->text('titulos_premios')->nullable()->after('seminarios');
        });
    }

    public function down(): void
    {
        Schema::table('hojas_anuales', function (Blueprint $table) {
            $table->dropColumn(['cursos', 'talleres', 'seminarios', 'titulos_premios']);
        });
    }
};
