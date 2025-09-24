<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class EventoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('eventos')->insert([
            [
                'id' => 1,
                'nombre' => 'Apoyo Vendimia',
                'fecha_inicio' => '2025-05-16',
                'fecha_termino' => '2025-05-23',
                'descripcion' => 'Apoyo en la vendimia con asistencia medica de respaldo y operativo de toma de presion',
                'tipo' => 'Asistencia de respaldo',
                'created_at' => null,
                'updated_at' => '2025-05-13 08:49:54',
            ],
            [
                'id' => 2,
                'nombre' => 'aa',
                'fecha_inicio' => '2025-05-03',
                'fecha_termino' => '2025-05-17',
                'descripcion' => 'asdf',
                'tipo' => 'asdf',
                'created_at' => '2025-05-12 04:02:18',
                'updated_at' => '2025-05-12 04:05:21',
            ],
        ]);
    }
}
