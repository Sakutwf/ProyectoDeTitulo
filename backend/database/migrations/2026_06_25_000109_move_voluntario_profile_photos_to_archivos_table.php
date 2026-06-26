<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('archivos') || ! Schema::hasColumn('voluntarios', 'foto_perfil')) {
            return;
        }

        $voluntarios = DB::table('voluntarios')
            ->select('id', 'foto_perfil', 'updated_at', 'created_at')
            ->whereNotNull('foto_perfil')
            ->where('foto_perfil', '!=', '')
            ->get();

        foreach ($voluntarios as $voluntario) {
            $archivoExistente = DB::table('archivos')
                ->where('entidad', 'voluntario')
                ->where('entidad_id', $voluntario->id)
                ->where('categoria', 'foto_perfil')
                ->exists();

            if ($archivoExistente) {
                continue;
            }

            $extension = pathinfo($voluntario->foto_perfil, PATHINFO_EXTENSION) ?: null;

            DB::table('archivos')->insert([
                'entidad' => 'voluntario',
                'entidad_id' => $voluntario->id,
                'categoria' => 'foto_perfil',
                'ruta' => $voluntario->foto_perfil,
                'nombre_original' => basename($voluntario->foto_perfil),
                'extension' => $extension,
                'mime_type' => null,
                'tamano' => null,
                'descripcion' => 'Foto de perfil del voluntario',
                'subido_por' => null,
                'created_at' => $voluntario->created_at,
                'updated_at' => $voluntario->updated_at,
            ]);
        }

        Schema::table('voluntarios', function (Blueprint $table) {
            $table->dropColumn('foto_perfil');
        });
    }

    public function down(): void
    {
        if (! Schema::hasColumn('voluntarios', 'foto_perfil')) {
            Schema::table('voluntarios', function (Blueprint $table) {
                $table->string('foto_perfil', 255)->nullable()->after('alergias');
            });
        }

        if (! Schema::hasTable('archivos')) {
            return;
        }

        $archivos = DB::table('archivos')
            ->select('entidad_id', 'ruta')
            ->where('entidad', 'voluntario')
            ->where('categoria', 'foto_perfil')
            ->get();

        foreach ($archivos as $archivo) {
            DB::table('voluntarios')
                ->where('id', $archivo->entidad_id)
                ->update(['foto_perfil' => $archivo->ruta]);
        }

        DB::table('archivos')
            ->where('entidad', 'voluntario')
            ->where('categoria', 'foto_perfil')
            ->delete();
    }
};
