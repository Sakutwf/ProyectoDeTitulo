<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ActivoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('activos')->insert([
            [
                'id' => 1,
                'filial_id' => 1,
                'donacion_id' => 1,
                'nombre' => 'Mesa',
                'cantidad' => 3,
                'es_arrendable' => 0, // 0 representa 'false'
                'created_at' => null,
                'updated_at' => null,
            ],
        ]);
    }
}
