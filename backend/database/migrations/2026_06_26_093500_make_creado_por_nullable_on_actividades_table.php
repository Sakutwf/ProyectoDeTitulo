<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('actividades', function (Blueprint $table) {
            $table->dropForeign(['creado_por']);
            $table->unsignedBigInteger('creado_por')->nullable()->change();
            $table->foreign('creado_por')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        $defaultUserId = DB::table('users')->orderBy('id')->value('id');

        if ($defaultUserId === null) {
            throw new RuntimeException('No existe un usuario para restaurar creado_por como obligatorio.');
        }

        DB::table('actividades')->whereNull('creado_por')->update([
            'creado_por' => $defaultUserId,
        ]);

        Schema::table('actividades', function (Blueprint $table) {
            $table->dropForeign(['creado_por']);
            $table->unsignedBigInteger('creado_por')->nullable(false)->change();
            $table->foreign('creado_por')->references('id')->on('users')->restrictOnDelete();
        });
    }
};
