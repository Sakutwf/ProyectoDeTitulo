<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('actividad_climas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('actividad_id')->constrained('actividades')->cascadeOnDelete();
            $table->foreignId('archivo_id')->nullable()->constrained('archivos')->nullOnDelete();
            $table->unsignedInteger('orden')->default(1);
            $table->decimal('temperatura_minima', 5, 2)->nullable();
            $table->decimal('temperatura_maxima', 5, 2)->nullable();
            $table->string('tipo_clima', 150)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('actividad_climas');
    }
};
