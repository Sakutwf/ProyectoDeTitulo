<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('actividad_voluntario', function (Blueprint $table) {
            $table->string('estado', 20)->default('aprobada')->after('horas_asistidas')->index();
            $table->foreignId('revisado_por')->nullable()->after('registrado_por')->constrained('users')->nullOnDelete();
            $table->timestamp('revisado_en')->nullable()->after('revisado_por');
        });
    }

    public function down(): void
    {
        Schema::table('actividad_voluntario', function (Blueprint $table) {
            $table->dropForeign(['revisado_por']);
            $table->dropIndex(['estado']);
            $table->dropColumn(['estado', 'revisado_por', 'revisado_en']);
        });
    }
};
