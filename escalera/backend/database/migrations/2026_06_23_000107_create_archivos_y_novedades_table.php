<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('archivos', function (Blueprint $table) {
            $table->id();
            $table->string('entidad', 100);
            $table->unsignedBigInteger('entidad_id');
            $table->string('categoria', 100);
            $table->string('ruta', 255);
            $table->string('nombre_original', 255)->nullable();
            $table->string('extension', 20)->nullable();
            $table->string('mime_type', 100)->nullable();
            $table->unsignedBigInteger('tamano')->nullable();
            $table->text('descripcion')->nullable();
            $table->foreignId('subido_por')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['entidad', 'entidad_id']);
        });

        Schema::create('novedades', function (Blueprint $table) {
            $table->id();
            $table->foreignId('actividad_id')->constrained('actividades')->cascadeOnDelete();
            $table->foreignId('archivo_portada_id')->nullable()->constrained('archivos')->nullOnDelete();
            $table->string('slug', 180)->unique();
            $table->string('titulo', 180);
            $table->text('resumen')->nullable();
            $table->text('contenido')->nullable();
            $table->foreignId('creado_por')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('novedades');
        Schema::dropIfExists('archivos');
    }
};
