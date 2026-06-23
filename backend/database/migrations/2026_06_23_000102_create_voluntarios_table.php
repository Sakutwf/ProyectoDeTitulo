<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('voluntarios', function (Blueprint $table) {
            $table->string('n_registro', 30)->primary();
            $table->foreignId('user_id')->unique()->constrained('users')->cascadeOnDelete();
            $table->foreignId('filial_id')->constrained('filiales');
            $table->string('rut', 20)->unique();
            $table->string('nombres', 150);
            $table->string('apellidos', 150);
            $table->string('nacionalidad', 100)->nullable();
            $table->date('fecha_nacimiento')->nullable();
            $table->date('fecha_incorporacion')->nullable();
            $table->string('celular', 30)->nullable();
            $table->string('domicilio', 255)->nullable();
            $table->text('enfermedades')->nullable();
            $table->text('alergias')->nullable();
            $table->string('foto_perfil', 255)->nullable();
            $table->string('contacto_emergencia_nombre', 150)->nullable();
            $table->string('contacto_emergencia_numero', 30)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('voluntarios');
    }
};
