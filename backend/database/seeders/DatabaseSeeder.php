<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;


class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::create([
            "rut" => "11.111.111-1",
            "nombre"=> "Sebastian",
            "email"=> "sebastian@gmail.com",
            "telefono"=> "41232131",
            "fecha_nacimiento"=> "2022-02-03",
            "fecha_ingreso"=> "2022-02-04",
            "grupo_sanguineo"=> "2022-02-05",
            "factor_rh"=> "de",
            "password"=> "Sterek64",
            "role"=> "user"
        ]);

        User::create([
            "rut" => "22.222.222-2",
            "nombre"=> "scarlet",
            "email"=> "scarlet@gmail.com",
            "telefono"=> "41232131",
            "fecha_nacimiento"=> "2022-02-03",
            "fecha_ingreso"=> "2022-02-04",
            "grupo_sanguineo"=> "2022-02-05",
            "factor_rh"=> "de",
            "password"=> "Sterek64",
            "role"=> "user"
        ]);

        User::create([
            "rut" => "33.333.333-3",
            "nombre"=> "miau",
            "email"=> "miau@gmail.com",
            "telefono"=> "41232131",
            "fecha_nacimiento"=> "2022-02-03",
            "fecha_ingreso"=> "2022-02-04",
            "grupo_sanguineo"=> "2022-02-05",
            "factor_rh"=> "de",
            "password"=> "Sterek64",
            "role"=> "user"
        ]);
    }
}
