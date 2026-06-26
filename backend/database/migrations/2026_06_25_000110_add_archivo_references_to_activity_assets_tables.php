<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('galerias_actividad', function (Blueprint $table) {
            if (! Schema::hasColumn('galerias_actividad', 'archivo_id')) {
                $table->foreignId('archivo_id')->nullable()->after('actividad_id')->constrained('archivos')->nullOnDelete();
            }
        });

        Schema::table('boletas_viatico', function (Blueprint $table) {
            if (! Schema::hasColumn('boletas_viatico', 'archivo_id')) {
                $table->foreignId('archivo_id')->nullable()->after('voluntario_id')->constrained('archivos')->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('boletas_viatico', function (Blueprint $table) {
            if (Schema::hasColumn('boletas_viatico', 'archivo_id')) {
                $table->dropConstrainedForeignId('archivo_id');
            }
        });

        Schema::table('galerias_actividad', function (Blueprint $table) {
            if (Schema::hasColumn('galerias_actividad', 'archivo_id')) {
                $table->dropConstrainedForeignId('archivo_id');
            }
        });
    }
};
