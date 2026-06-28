<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('voluntarios', function (Blueprint $table) {
            if (! Schema::hasColumn('voluntarios', 'foto_perfil')) {
                $table->string('foto_perfil', 255)->nullable()->after('alergias');
            }
        });
    }

    public function down(): void
    {
        Schema::table('voluntarios', function (Blueprint $table) {
            if (Schema::hasColumn('voluntarios', 'foto_perfil')) {
                $table->dropColumn('foto_perfil');
            }
        });
    }
};
