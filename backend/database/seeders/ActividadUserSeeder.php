<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ActividadUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('actividad_user')->insert([
            [
                'id' => 11,
                'actividad_id' => 20,
                'user_id' => 4,
                'created_at' => '2025-06-20 15:37:39',
                'updated_at' => '2025-06-20 15:37:39',
            ],
            [
                'id' => 12,
                'actividad_id' => 20,
                'user_id' => 6,
                'created_at' => '2025-06-20 15:37:39',
                'updated_at' => '2025-06-20 15:37:39',
            ],
            [
                'id' => 13,
                'actividad_id' => 20,
                'user_id' => 28, // Corregido de 26 a 28
                'created_at' => '2025-06-20 15:37:39',
                'updated_at' => '2025-06-20 15:37:39',
            ],
            [
                'id' => 17,
                'actividad_id' => 13,
                'user_id' => 2,
                'created_at' => '2025-06-20 16:01:29',
                'updated_at' => '2025-06-20 16:01:29',
            ],
            [
                'id' => 18,
                'actividad_id' => 10,
                'user_id' => 4,
                'created_at' => '2025-06-20 16:03:24',
                'updated_at' => '2025-06-20 16:03:24',
            ],
            [
                'id' => 19,
                'actividad_id' => 22,
                'user_id' => 3,
                'created_at' => '2025-06-20 16:04:35',
                'updated_at' => '2025-06-20 16:04:35',
            ],
            [
                'id' => 20,
                'actividad_id' => 22,
                'user_id' => 4,
                'created_at' => '2025-06-20 16:04:35',
                'updated_at' => '2025-06-20 16:04:35',
            ],
            [
                'id' => 21,
                'actividad_id' => 22,
                'user_id' => 6,
                'created_at' => '2025-06-20 16:04:35',
                'updated_at' => '2025-06-20 16:04:35',
            ],
            [
                'id' => 22,
                'actividad_id' => 22,
                'user_id' => 28, // Corregido de 26 a 28
                'created_at' => '2025-06-20 16:04:35',
                'updated_at' => '2025-06-20 16:04:35',
            ],
            [
                'id' => 23,
                'actividad_id' => 23,
                'user_id' => 3,
                'created_at' => '2025-06-20 16:08:18',
                'updated_at' => '2025-06-20 16:08:18',
            ],
            [
                'id' => 24,
                'actividad_id' => 23,
                'user_id' => 4,
                'created_at' => '2025-06-20 16:08:18',
                'updated_at' => '2025-06-20 16:08:18',
            ],
            [
                'id' => 25,
                'actividad_id' => 23,
                'user_id' => 6,
                'created_at' => '2025-06-20 16:08:18',
                'updated_at' => '2025-06-20 16:08:18',
            ],
        ]);
    }
}
