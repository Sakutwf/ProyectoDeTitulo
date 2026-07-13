<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('portada_ajustes', function (Blueprint $table) {
            $table->string('telefono', 40)->nullable();
            $table->string('instagram_url', 500)->nullable();
            $table->string('facebook_url', 500)->nullable();
            $table->json('directorio')->nullable();
            $table->json('enlaces_relacionados')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('portada_ajustes', fn (Blueprint $table) => $table->dropColumn([
            'telefono', 'instagram_url', 'facebook_url', 'directorio', 'enlaces_relacionados',
        ]));
    }
};
