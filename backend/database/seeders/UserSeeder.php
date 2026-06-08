<?php

namespace Database\Seeders;

use App\Models\AntecedenteVoluntario;
use App\Models\HojaAnual;
use App\Models\Role;
use App\Models\User;
use App\Models\Voluntario;
use Illuminate\Support\Carbon;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $administrador = User::updateOrCreate(
            ["rut" => "admin"],
            [
                "nombre"=> "Administrador",
                "email"=> "admin@cruzroja.local",
                "telefono"=> "admin",
                "estado"=> "ACTIVO",
                "password"=> "admin",
            ]
        );
        $administrador->roles()->sync($this->roleIds(['administrador']));
        Voluntario::where('user_id', $administrador->id)->delete();

        $voluntaria = User::create([
            "rut" => "22.222.222-2",
            "nombre"=> "Scarlet Diaz",
            "email"=> "scarlet@gmail.com",
            "telefono"=> "41232131",
            "estado"=> "ACTIVO",
            "password"=> "cruzroja26",
        ]);
        $voluntaria->roles()->sync($this->roleIds(['voluntario']));

        $voluntariaRegistro = Voluntario::create([
            "user_id" => $voluntaria->id,
            "fecha_ingreso"=> "2022-02-04",
            "n_registro"=> "00001",
            "grupo_sanguineo"=> "A",
            "factor_rh"=> "+",
            "fecha_nacimiento"=> "2000-02-03",
        ]);

        $hojaDeVida = $voluntariaRegistro->hojaDeVida()->create([
            "fecha_creacion" => "2022-02-04",
            "estado" => "ACTIVO",
        ]);

        HojaAnual::create([
            "hoja_de_vida_id" => $hojaDeVida->id_libro,
            "anio" => 2024,
            "porcentaje_asistencia" => 92.50,
            "cargo" => "Brigadista",
            "observaciones_generales" => "Participacion destacada durante el periodo.",
        ]);

        AntecedenteVoluntario::create([
            "hoja_de_vida_id" => $hojaDeVida->id_libro,
            "tipo" => "CURSO",
            "nombre" => "Primeros Auxilios",
            "descripcion" => "Curso base de atencion prehospitalaria.",
            "fecha_inicio" => "2023-03-01",
            "fecha_termino" => "2023-03-30",
            "duracion" => "30 dias",
        ]);

        $voluntario = User::create([
            "rut" => "33.333.333-3",
            "nombre"=> "Matias Rojas",
            "email"=> "miau@gmail.com",
            "telefono"=> "41232131",
            "estado"=> "ACTIVO",
            "password"=> "cruzroja26",
        ]);
        $voluntario->roles()->sync($this->roleIds(['voluntario', 'secretario-directiva']));

        $voluntarioRegistro = Voluntario::create([
            "user_id" => $voluntario->id,
            "fecha_ingreso"=> "2021-05-10",
            "n_registro"=> "144301",
            "grupo_sanguineo"=> "O",
            "factor_rh"=> "-",
            "fecha_nacimiento"=> "1998-11-15",
        ]);

        $hojaDeVida = $voluntarioRegistro->hojaDeVida()->create([
            "fecha_creacion" => "2021-05-10",
            "estado" => "INACTIVO",
        ]);

        HojaAnual::create([
            "hoja_de_vida_id" => $hojaDeVida->id_libro,
            "anio" => 2023,
            "porcentaje_asistencia" => 78.00,
            "cargo" => "Apoyo Logistico",
            "observaciones_generales" => "Asistencia irregular en el ultimo trimestre.",
        ]);

        AntecedenteVoluntario::create([
            "hoja_de_vida_id" => $hojaDeVida->id_libro,
            "tipo" => "PREMIO",
            "nombre" => "Reconocimiento Regional",
            "descripcion" => "Premio por apoyo en operativos comunitarios.",
            "fecha_inicio" => "2022-12-01",
            "fecha_termino" => "2022-12-01",
            "duracion" => "1 dia",
        ]);

        $finanzas = User::create([
            "rut" => "44.444.444-4",
            "nombre"=> "Javiera Fuentes",
            "email"=> "javiera@gmail.com",
            "telefono"=> "41232131",
            "estado"=> "ACTIVO",
            "password"=> "cruzroja26",
        ]);
        $finanzas->roles()->sync($this->roleIds(['encargada-finanzas']));
        $this->upsertVolunteerLoginProfile($finanzas, '90002');
    }

    private function roleIds(array $slugs): array
    {
        return Role::whereIn('slug', $slugs)->pluck('id')->all();
    }

    private function upsertVolunteerLoginProfile(User $user, string $registro): void
    {
        Voluntario::updateOrCreate(
            ['user_id' => $user->id],
            [
                'fecha_ingreso' => Carbon::create(2020, 1, 1)->toDateString(),
                'n_registro' => $registro,
                'factor_rh' => '+',
                'grupo_sanguineo' => 'O',
                'fecha_nacimiento' => Carbon::create(1990, 1, 1)->toDateString(),
            ]
        );
    }
}
