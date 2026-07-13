<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('portada_ajustes')->where('novedades_etiqueta_color', '#d72732')->update(['novedades_etiqueta_color' => '#ffffff']);
        DB::table('portada_ajustes')->where('novedades_titulo_color', '#011e41')->update(['novedades_titulo_color' => '#ffffff']);
        DB::table('portada_ajustes')->where('novedades_descripcion_color', '#5f6b7c')->update(['novedades_descripcion_color' => '#ffe3e5']);
    }

    public function down(): void
    {
        DB::table('portada_ajustes')->where('novedades_etiqueta_color', '#ffffff')->update(['novedades_etiqueta_color' => '#d72732']);
        DB::table('portada_ajustes')->where('novedades_titulo_color', '#ffffff')->update(['novedades_titulo_color' => '#011e41']);
        DB::table('portada_ajustes')->where('novedades_descripcion_color', '#ffe3e5')->update(['novedades_descripcion_color' => '#5f6b7c']);
    }
};
