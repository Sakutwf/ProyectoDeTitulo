<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use App\Models\Voluntario;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $filialId = DB::table('filiales')->updateOrInsert(
            ['nombre' => 'Filial Santiago Centro'],
            [
                'comite_regional' => 'Metropolitano',
                'direccion' => 'Av. Principal 123',
                'comuna' => 'Santiago',
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );

        $filial = DB::table('filiales')->where('nombre', 'Filial Santiago Centro')->first();

        $administrador = User::updateOrCreate(
            ['email' => 'admin@cruzroja.local'],
            [
                'name' => 'Administrador',
                'estado' => true,
                'password' => Hash::make('admin'),
            ]
        );
        $administrador->roles()->sync($this->roleIds(['administrador']));
        Voluntario::where('user_id', $administrador->id)->delete();

        $voluntaria = User::updateOrCreate([
            'email' => 'scarlet@gmail.com',
        ], [
            'name' => 'Scarlet Diaz',
            'estado' => true,
            'password' => Hash::make('cruzRojaCco26'),
        ]);
        $voluntaria->roles()->sync($this->roleIds(['voluntario']));

        Voluntario::updateOrCreate([
            'user_id' => $voluntaria->id,
        ], [
            'n_registro' => '00001',
            'filial_id' => $filial->id,
            'rut' => '22.222.222-2',
            'nombres' => 'Scarlet',
            'apellidos' => 'Diaz',
            'nacionalidad' => 'Chilena',
            'fecha_nacimiento' => '2000-02-03',
            'fecha_incorporacion' => '2022-02-04',
            'celular' => '41232131',
        ]);

        $voluntario = User::updateOrCreate([
            'email' => 'matias@gmail.com',
        ], [
            'name' => 'Matias Rojas',
            'estado' => true,
            'password' => Hash::make('cruzRojaCco26'),
        ]);
        $voluntario->roles()->sync($this->roleIds(['voluntario', 'secretario-directiva']));

        Voluntario::updateOrCreate([
            'user_id' => $voluntario->id,
        ], [
            'n_registro' => '144301',
            'filial_id' => $filial->id,
            'rut' => '33.333.333-3',
            'nombres' => 'Matias',
            'apellidos' => 'Rojas',
            'nacionalidad' => 'Chilena',
            'fecha_nacimiento' => '1998-11-15',
            'fecha_incorporacion' => '2021-05-10',
            'celular' => '41232131',
        ]);

        DB::table('hojas_vida_anuales')->updateOrInsert(
            ['voluntario_n_registro' => '00001', 'anio' => 2024],
            [
                'asistencia_anual_horas' => 92.5,
                'asistencia_anual_porcentaje' => 92.5,
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );

        DB::table('hojas_vida_anuales')->updateOrInsert(
            ['voluntario_n_registro' => '144301', 'anio' => 2023],
            [
                'asistencia_anual_horas' => 78,
                'asistencia_anual_porcentaje' => 78,
                'comentarios' => 'Asistencia irregular en el ultimo trimestre.',
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );

        $finanzas = User::updateOrCreate([
            'email' => 'javiera@gmail.com',
        ], [
            'name' => 'Javiera Fuentes',
            'estado' => true,
            'password' => Hash::make('cruzRojaCco26'),
        ]);
        $finanzas->roles()->sync($this->roleIds(['encargada-finanzas']));
        Voluntario::updateOrCreate(
            ['user_id' => $finanzas->id],
            [
                'n_registro' => '90002',
                'filial_id' => $filial->id,
                'rut' => '44.444.444-4',
                'nombres' => 'Javiera',
                'apellidos' => 'Fuentes',
                'nacionalidad' => 'Chilena',
                'fecha_nacimiento' => '1990-01-01',
                'fecha_incorporacion' => '2020-01-01',
                'celular' => '41232131',
            ]
        );
    }

    private function roleIds(array $claves): array
    {
        return Role::whereIn('clave', $claves)->pluck('id')->all();
    }
}
