<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DonacionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('donacions')->insert([
            [
                'id' => 1,
                'nombre' => 'Mesas',
                'tipo' => 'Activo',
                'cantidad' => 3,
                'fecha' => '2025-04-23',
                'created_at' => null,
                'updated_at' => null,
            ],
        ]);
    }
}
