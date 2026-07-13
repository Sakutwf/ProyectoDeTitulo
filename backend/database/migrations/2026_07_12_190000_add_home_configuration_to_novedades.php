<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('novedades', function (Blueprint $table) {
            $table->unsignedInteger('orden')->default(0)->after('contenido');
            $table->string('ancho', 20)->default('tercio')->after('orden');
            $table->json('campos_visibles')->nullable()->after('ancho');
            $table->unsignedInteger('personas_ayudadas')->nullable()->after('campos_visibles');
            $table->boolean('publicada')->default(true)->after('campos_visibles');
        });

        Schema::create('carrusel_inicio', function (Blueprint $table) {
            $table->id();
            $table->string('titulo', 180)->nullable();
            $table->text('bajada')->nullable();
            $table->unsignedInteger('orden')->default(0);
            $table->boolean('publicada')->default(true);
            $table->foreignId('creado_por')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('carrusel_inicio_imagenes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('carrusel_inicio_id')->constrained('carrusel_inicio')->cascadeOnDelete();
            $table->foreignId('archivo_id')->constrained('archivos')->cascadeOnDelete();
            $table->unsignedTinyInteger('orden')->default(0);
            $table->unique(['carrusel_inicio_id', 'archivo_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('carrusel_inicio_imagenes');
        Schema::dropIfExists('carrusel_inicio');

        Schema::table('novedades', function (Blueprint $table) {
            $table->dropColumn(['orden', 'ancho', 'campos_visibles', 'personas_ayudadas', 'publicada']);
        });
    }
};
