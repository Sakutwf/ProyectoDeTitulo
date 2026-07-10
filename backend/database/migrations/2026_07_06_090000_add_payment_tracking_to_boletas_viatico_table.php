<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('boletas_viatico', function (Blueprint $table) {
            if (! Schema::hasColumn('boletas_viatico', 'fecha_pago')) {
                $table->date('fecha_pago')->nullable()->after('estado');
            }
        });

        DB::table('boletas_viatico')
            ->whereNull('estado')
            ->orWhere('estado', '')
            ->update(['estado' => 'solicitado']);

        DB::table('boletas_viatico')
            ->where('estado', 'pendiente')
            ->update(['estado' => 'solicitado']);

        DB::table('boletas_viatico')
            ->where('estado', 'pagada')
            ->update([
                'estado' => 'pagado',
                'fecha_pago' => DB::raw('COALESCE(fecha_pago, DATE(updated_at), DATE(created_at))'),
            ]);
    }

    public function down(): void
    {
        DB::table('boletas_viatico')
            ->where('estado', 'solicitado')
            ->update(['estado' => 'pendiente']);

        DB::table('boletas_viatico')
            ->where('estado', 'pagado')
            ->update(['estado' => 'pagada']);

        Schema::table('boletas_viatico', function (Blueprint $table) {
            if (Schema::hasColumn('boletas_viatico', 'fecha_pago')) {
                $table->dropColumn('fecha_pago');
            }
        });
    }
};
