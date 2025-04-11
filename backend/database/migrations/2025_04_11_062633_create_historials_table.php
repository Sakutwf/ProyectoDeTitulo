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

        /**
         * los datos son:
         * user_id
         * año
         * cargo
         * taller
         * curso
         * seminario
         * porcentaje_de_asistencia
         * titulos
         * premios
         * observaciones
         * estudios
         */
        Schema::create('historials', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->year('año');
            $table->string('cargo')->nullable();
            $table->string('taller')->nullable();
            $table->string('curso')->nullable();
            $table->string('seminario')->nullable();
            $table->string('porcentaje_de_asistencia')->nullable();
            $table->string('titulos')->nullable();
            $table->string('premios')->nullable();
            $table->string('observaciones')->nullable();
            $table->string('estudios')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('historials');
    }
};
