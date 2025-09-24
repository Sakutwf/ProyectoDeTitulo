<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TituloSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('titulos')->insert([
            [
                'id' => 1,
                'user_id' => 28,
                'tipo' => 'taller',
                'nombre' => 'Primeros auxilios',
                'fecha' => '2025-05-11',
                'observaciones' => null,
                'created_at' => null,
                'updated_at' => null,
            ],
            [
                'id' => 2,
                'user_id' => 2,
                'tipo' => 'taller',
                'nombre' => 'Primeros auxilios',
                'fecha' => '2025-05-11',
                'observaciones' => null,
                'created_at' => null,
                'updated_at' => null,
            ],
            [
                'id' => 3,
                'user_id' => 3,
                'tipo' => 'taller',
                'nombre' => 'Primeros auxilios',
                'fecha' => '2025-05-11',
                'observaciones' => null,
                'created_at' => null,
                'updated_at' => null,
            ],
            [
                'id' => 4,
                'user_id' => 4,
                'tipo' => 'taller',
                'nombre' => 'Primeros auxilios',
                'fecha' => '2025-05-11',
                'observaciones' => null,
                'created_at' => null,
                'updated_at' => null,
            ],
            [
                'id' => 5,
                'user_id' => 6,
                'tipo' => 'taller',
                'nombre' => 'Primeros auxilios',
                'fecha' => '2025-05-11',
                'observaciones' => null,
                'created_at' => null,
                'updated_at' => null,
            ],
        ]);
    }
}
