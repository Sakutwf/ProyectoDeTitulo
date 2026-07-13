<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('campanas_notificacion', function (Blueprint $table) {
            $table->id();
            $table->string('tipo', 60);
            $table->nullableMorphs('asunto');
            $table->string('asunto_correo', 200);
            $table->text('mensaje')->nullable();
            $table->json('metadatos')->nullable();
            $table->string('huella', 64)->nullable()->index();
            $table->string('estado', 30)->default('encolada');
            $table->foreignId('autorizada_por')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('autorizada_en')->nullable();
            $table->timestamps();
            $table->index(['tipo', 'asunto_type', 'asunto_id']);
        });

        Schema::create('destinatarios_notificacion', function (Blueprint $table) {
            $table->id();
            $table->foreignId('campana_id')->constrained('campanas_notificacion')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('nombre', 200)->nullable();
            $table->string('correo', 255);
            $table->string('estado', 30)->default('pendiente');
            $table->text('error')->nullable();
            $table->timestamp('enviado_en')->nullable();
            $table->timestamps();
            $table->unique(['campana_id', 'correo']);
        });

        Schema::create('solicitudes_hoja_vida', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hoja_vida_anual_id')->constrained('hoja_vida_anual')->cascadeOnDelete();
            $table->foreignId('voluntario_id')->constrained('voluntarios')->cascadeOnDelete();
            $table->string('tipo_registro', 40);
            $table->string('accion', 20);
            $table->unsignedBigInteger('registro_id')->nullable();
            $table->json('datos');
            $table->json('archivo_ids_conservados')->nullable();
            $table->string('estado', 30)->default('pendiente');
            $table->foreignId('solicitada_por')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('revisada_por')->nullable()->constrained('users')->nullOnDelete();
            $table->text('motivo_revision')->nullable();
            $table->timestamp('revisada_en')->nullable();
            $table->timestamps();
            $table->index(['estado', 'voluntario_id']);
        });

        Schema::table('boletas_viatico', function (Blueprint $table) {
            $table->text('motivo_revision')->nullable()->after('estado');
        });
    }

    public function down(): void
    {
        Schema::table('boletas_viatico', fn (Blueprint $table) => $table->dropColumn('motivo_revision'));
        Schema::dropIfExists('solicitudes_hoja_vida');
        Schema::dropIfExists('destinatarios_notificacion');
        Schema::dropIfExists('campanas_notificacion');
    }
};
