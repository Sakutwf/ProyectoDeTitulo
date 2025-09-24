<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ActividadSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('actividads')->insert([
            [
                'id' => 10,
                'evento_id' => 1,
                'tipo' => 'AAAAa',
                'N_beneficiarios' => 6,
                'created_at' => '2025-05-23 11:51:47',
                'updated_at' => '2025-05-23 11:51:47',
            ],
            [
                'id' => 13,
                'evento_id' => 1,
                'tipo' => 'operativo',
                'N_beneficiarios' => null,
                'created_at' => '2025-06-12 16:22:53',
                'updated_at' => '2025-06-12 16:22:53',
            ],
            [
                'id' => 20,
                'evento_id' => 1,
                'tipo' => 'operativo',
                'N_beneficiarios' => 3,
                'created_at' => '2025-06-20 15:37:33',
                'updated_at' => '2025-06-20 15:37:33',
            ],
            [
                'id' => 22,
                'evento_id' => 1,
                'tipo' => 'AA',
                'N_beneficiarios' => 0,
                'created_at' => '2025-06-20 16:04:30',
                'updated_at' => '2025-06-20 16:04:30',
            ],
            [
                'id' => 23,
                'evento_id' => 1,
                'tipo' => 'AA',
                'N_beneficiarios' => 2,
                'created_at' => '2025-06-20 16:08:15',
                'updated_at' => '2025-06-20 16:08:15',
            ],
        ]);
    }
}
