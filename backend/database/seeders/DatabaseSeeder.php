<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            // --- 1. Tablas base sin dependencias ---
            // Se ejecutan primero porque otras tablas dependen de ellas.
            RoleSeeder::class,
            FilialSeeder::class,
            EventoSeeder::class,
            DonacionSeeder::class,

            // --- 2. Tablas que dependen del grupo 1 ---
            // 'users' depende de 'roles' y 'filials'.
            UserSeeder::class,
            // 'actividads' depende de 'eventos'.
            ActividadSeeder::class,
            // 'activos' depende de 'filials' y 'donacions'.
            ActivoSeeder::class,

            // --- 3. Tablas que dependen de 'users' ---
            // 'titulos' y 'premios' están asociados a un usuario.
            TituloSeeder::class,
            PremioSeeder::class,

            // --- 4. Tablas Pivote / Relaciones Complejas ---
            // 'actividad_user' depende de 'actividads' y 'users'.
            ActividadUserSeeder::class,
        ]);
    }
}
