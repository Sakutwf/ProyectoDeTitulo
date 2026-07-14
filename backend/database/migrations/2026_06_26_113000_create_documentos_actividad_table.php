<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('documentos_actividad')) {
            Schema::create('documentos_actividad', function (Blueprint $table) {
                $table->id();
                $table->foreignId('actividad_id')->constrained('actividades')->cascadeOnDelete();
                $table->string('tipo_documento', 60);
                $table->string('titulo', 200);
                $table->string('estado', 30)->default('borrador');
                $table->date('fecha_documento')->nullable();
                $table->json('datos_contexto')->nullable();
                $table->json('contenido')->nullable();
                $table->string('ruta_pdf')->nullable();
                $table->foreignId('generado_por')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();

                $table->index(['actividad_id', 'tipo_documento']);
            });

            return;
        }

        Schema::table('documentos_actividad', function (Blueprint $table) {
            if (!Schema::hasColumn('documentos_actividad', 'titulo')) {
                $table->string('titulo', 200)->default('Documento sin titulo')->after('tipo_documento');
            }

            if (!Schema::hasColumn('documentos_actividad', 'estado')) {
                $table->string('estado', 30)->default('borrador')->after('titulo');
            }

            if (!Schema::hasColumn('documentos_actividad', 'fecha_documento')) {
                $table->date('fecha_documento')->nullable()->after('estado');
            }

            if (!Schema::hasColumn('documentos_actividad', 'datos_contexto')) {
                $table->json('datos_contexto')->nullable()->after('fecha_documento');
            }

            if (!Schema::hasColumn('documentos_actividad', 'contenido')) {
                $table->json('contenido')->nullable()->after('datos_contexto');
            }
        });

        if (Schema::hasColumn('documentos_actividad', 'fecha_generacion') && Schema::hasColumn('documentos_actividad', 'fecha_documento')) {
            DB::table('documentos_actividad')
                ->whereNull('fecha_documento')
                ->update(['fecha_documento' => DB::raw('fecha_generacion')]);
        }

        if (Schema::hasColumn('documentos_actividad', 'titulo')) {
            DB::table('documentos_actividad')
                ->where(function ($query) {
                    $query->whereNull('titulo')->orWhere('titulo', '');
                })
                ->pluck('id')
                ->each(fn ($id) => DB::table('documentos_actividad')
                    ->where('id', $id)
                    ->update(['titulo' => "Documento {$id}"]));
        }

        if (Schema::hasColumn('documentos_actividad', 'estado')) {
            DB::table('documentos_actividad')
                ->whereNull('estado')
                ->update(['estado' => 'borrador']);
        }
    }

    public function down(): void
    {
        if (!Schema::hasTable('documentos_actividad')) {
            return;
        }

        if (!Schema::hasColumn('documentos_actividad', 'fecha_generacion')) {
            Schema::dropIfExists('documentos_actividad');
        }
    }
};
