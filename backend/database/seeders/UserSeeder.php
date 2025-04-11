<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

use App\Models\User;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
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
            "role_id"=> 1
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
            "role_id"=> 2
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
            "role_id"=> 2
        ]);
    }
}
