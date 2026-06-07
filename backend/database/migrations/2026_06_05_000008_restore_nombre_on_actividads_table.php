<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('actividads', 'nombre')) {
            Schema::table('actividads', function (Blueprint $table) {
                $table->string('nombre')->nullable()->after('evento_id');
            });
        }

        if (Schema::hasColumn('actividads', 'nombre_actividad')) {
            DB::table('actividads')
                ->whereNull('nombre')
                ->orWhere('nombre', '')
                ->update([
                    'nombre' => DB::raw('nombre_actividad'),
                ]);
        }
    }

    public function down(): void
    {
        // Se mantiene sin revertir para no perder el nombre restaurado.
    }
};
