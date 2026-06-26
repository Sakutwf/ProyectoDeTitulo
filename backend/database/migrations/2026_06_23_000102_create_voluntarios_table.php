<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('voluntarios', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->unique()->constrained('users')->nullOnDelete();
            $table->foreignId('filial_id')->constrained('filiales')->restrictOnDelete();
            $table->string('registro_filial', 50);
            $table->string('rut', 20)->unique();
            $table->string('nombres', 150);
            $table->string('apellidos', 150);
            $table->string('nacionalidad', 100)->nullable();
            $table->date('fecha_nacimiento')->nullable();
            $table->date('fecha_incorporacion')->nullable();
            $table->string('nivel_escolaridad', 100)->nullable();
            $table->string('estado_civil', 100)->nullable();
            $table->string('ocupacion', 150)->nullable();
            $table->string('grupo_sanguineo', 20)->nullable();
            $table->string('correo_electronico', 150)->nullable();
            $table->string('celular', 30)->nullable();
            $table->string('domicilio', 255)->nullable();
            $table->text('enfermedades')->nullable();
            $table->text('alergias')->nullable();
            $table->string('contacto_emergencia_nombre', 150)->nullable();
            $table->string('contacto_emergencia_numero', 30)->nullable();
            $table->timestamps();

            $table->unique(['filial_id', 'registro_filial']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('voluntarios');
    }
};
