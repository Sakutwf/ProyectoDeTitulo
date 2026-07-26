<?php

namespace Database\Seeders;

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
        $timestamp = now();

        $this->deleteSeedUsers();

        DB::table('filiales')->updateOrInsert(
            ['cut' => '07301'],
            [
                'id' => 1,
                'nombre' => 'Curicó',
                'comite_regional' => 'Maule',
                'direccion' => 'Estado N°206',
                'comuna' => 'Curicó',
                'updated_at' => $timestamp,
                'created_at' => $timestamp,
            ]
        );

        $filialId = DB::table('filiales')
            ->where('cut', '07301')
            ->value('id');

        $adminUserId = $this->upsertUser(
            'admin',
            'admin',
            false
        );

        $marianaUserId = $this->upsertUser(
            '22.222.222-2',
            'cruzRojaCco26',
            false
        );

        $andresUserId = $this->upsertUser(
            '33.333.333-3',
            'cruzRojaCco26',
            false
        );

        $javieraUserId = $this->upsertUser(
            '44.444.444-4',
            'cruzRojaCco26',
            false
        );

        $this->syncUserRoles($adminUserId, ['administrador']);
        $this->syncUserRoles($marianaUserId, ['voluntario']);
        $this->syncUserRoles($andresUserId, ['voluntario', 'secretario']);
        $this->syncUserRoles($javieraUserId, ['finanzas']);

        $marianaVoluntarioId = $this->upsertVoluntario(
            $marianaUserId,
            $filialId,
            '00001',
            [
                'rut' => '22.222.222-2',
                'nombres' => 'Mariana',
                'apellidos' => 'Lopez',
                'nacionalidad' => 'Chilena',
                'fecha_nacimiento' => '2000-02-03',
                'fecha_incorporacion' => '2022-02-04',
                'correo_electronico' => 'mlopez@gmail.com',
                'celular' => '99546773',
            ]
        );

        $andresVoluntarioId = $this->upsertVoluntario(
            $andresUserId,
            $filialId,
            '144301',
            [
                'rut' => '33.333.333-3',
                'nombres' => 'Andres',
                'apellidos' => 'Rojas',
                'nacionalidad' => 'Chilena',
                'fecha_nacimiento' => '1998-11-15',
                'fecha_incorporacion' => '2021-05-10',
                'correo_electronico' => 'andres@gmail.com',
                'celular' => '99232131',
            ]
        );

        $this->upsertVoluntario(
            $javieraUserId,
            $filialId,
            '90002',
            [
                'rut' => '44.444.444-4',
                'nombres' => 'Javiera',
                'apellidos' => 'Fuentes',
                'nacionalidad' => 'Chilena',
                'fecha_nacimiento' => '1990-01-01',
                'fecha_incorporacion' => '2020-01-01',
                'correo_electronico' => 'javiera@gmail.com',
                'celular' => '98232131',
            ]
        );

        DB::table('hoja_vida_anual')->updateOrInsert(
            ['voluntario_id' => $marianaVoluntarioId, 'anio' => 2024],
            [
                'asistencia_anual_horas' => 120,
                'asistencia_anual_porcentaje' => 92.5,
                'updated_at' => $timestamp,
                'created_at' => $timestamp,
            ]
        );

        DB::table('hoja_vida_anual')->updateOrInsert(
            ['voluntario_id' => $andresVoluntarioId, 'anio' => 2023],
            [
                'asistencia_anual_horas' => 98,
                'asistencia_anual_porcentaje' => 98,
                'comentarios' => 'Asistencia irregular en el ultimo trimestre.',
                'updated_at' => $timestamp,
                'created_at' => $timestamp,
            ]
        );
    }

    private function upsertUser(string $username, string $password, bool $mustChangePassword): int
    {
        $timestamp = now();

        DB::table('users')->updateOrInsert(
            ['username' => $username],
            [
                'password' => Hash::make($password),
                'must_change_password' => $mustChangePassword,
                'updated_at' => $timestamp,
                'created_at' => $timestamp,
            ]
        );

        return (int) DB::table('users')
            ->where('username', $username)
            ->value('id');
    }

    private function deleteSeedUsers(): void
    {
        $userIds = DB::table('users')
            ->whereIn('username', [
                'admin',
                '22.222.222-2',
                '33.333.333-3',
                '44.444.444-4',
                '222222222',
                '333333333',
                '444444444',
            ])
            ->pluck('id');

        if ($userIds->isEmpty()) {
            return;
        }

        DB::table('voluntarios')->whereIn('user_id', $userIds)->delete();
        DB::table('users')->whereIn('id', $userIds)->delete();
    }

    private function syncUserRoles(int $userId, array $roleClaves): void
    {
        $roleIds = DB::table('roles')
            ->whereIn('clave', $roleClaves)
            ->pluck('id');

        foreach ($roleIds as $roleId) {
            DB::table('role_user')->updateOrInsert(
                [
                    'user_id' => $userId,
                    'role_id' => $roleId,
                ],
                [
                    'updated_at' => now(),
                    'created_at' => now(),
                ]
            );
        }
    }

    private function upsertVoluntario(int $userId, int $filialId, string $registroFilial, array $data): int
    {
        $timestamp = now();

        DB::table('voluntarios')->updateOrInsert(
            ['user_id' => $userId],
            array_merge($data, [
                'filial_id' => $filialId,
                'registro_filial' => $registroFilial,
                'updated_at' => $timestamp,
                'created_at' => $timestamp,
            ])
        );

        return (int) DB::table('voluntarios')
            ->where('user_id', $userId)
            ->value('id');
    }
}
