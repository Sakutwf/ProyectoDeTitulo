<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Antes de crear la tabla, elimínala si existe para evitar el error "already exists"
        Schema::dropIfExists('actividad_user');
        Schema::create('actividad_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('actividad_id')->constrained('actividads')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->timestamps();
            $table->unique(['actividad_id', 'user_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('actividad_user');
    }
};
