<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('actividads', function (Blueprint $table) {
            $table->decimal('horas_participacion', 6, 2)->default(1)->after('N_beneficiarios');
        });
    }

    public function down(): void
    {
        Schema::table('actividads', function (Blueprint $table) {
            $table->dropColumn('horas_participacion');
        });
    }
};
