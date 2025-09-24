<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PremioSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // La tabla 'premios' no contenía datos válidos en la imagen proporcionada.
        // Se deja este seeder vacío, listo para ser llenado en el futuro.

        // Ejemplo de cómo se vería con datos:

        DB::table('premios')->insert([
            [
                'user_id' => 1,
                'tipo' => 'otro',
                'nombre' => 'Voluntario del Año',
                'fecha' => '2024-12-20',
                'observaciones' => 'Premio por dedicación excepcional.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

    }
}
