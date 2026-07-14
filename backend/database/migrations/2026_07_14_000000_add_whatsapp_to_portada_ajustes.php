<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('portada_ajustes', function (Blueprint $table) {
            $table->string('whatsapp', 40)->nullable()->after('telefono');
        });
    }

    public function down(): void
    {
        Schema::table('portada_ajustes', fn (Blueprint $table) => $table->dropColumn('whatsapp'));
    }
};
