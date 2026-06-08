<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $now = now();
        $adminRoleId = DB::table('roles')->where('slug', 'administrador')->value('id');
        $finanzasRoleId = DB::table('roles')->where('slug', 'encargada-finanzas')->value('id');

        DB::table('users')
            ->join('voluntarios', 'voluntarios.user_id', '=', 'users.id')
            ->update([
                'users.password' => Hash::make('cruzroja26'),
                'users.updated_at' => $now,
            ]);

        DB::table('voluntarios')
            ->where('n_registro', 'VOL-001')
            ->update(['n_registro' => '00001']);

        DB::table('voluntarios')
            ->where('n_registro', 'VOL-002')
            ->update(['n_registro' => '144301']);

        $adminUserId = $adminRoleId
            ? DB::table('role_user')->where('role_id', $adminRoleId)->value('user_id')
            : null;

        if (! $adminUserId) {
            DB::table('users')->updateOrInsert(
                ['rut' => 'admin'],
                [
                    'nombre' => 'Administrador',
                    'email' => 'admin@cruzroja.local',
                    'telefono' => 'admin',
                    'estado' => 'ACTIVO',
                    'password' => Hash::make('cruzroja26'),
                    'updated_at' => $now,
                    'created_at' => $now,
                ]
            );

            $adminUserId = DB::table('users')->where('rut', 'admin')->value('id');
        }

        if ($adminRoleId && $adminUserId) {
            DB::table('role_user')->updateOrInsert(
                [
                    'user_id' => $adminUserId,
                    'role_id' => $adminRoleId,
                ],
                [
                    'created_at' => $now,
                    'updated_at' => $now,
                ]
            );
        }

        if ($adminUserId) {
            DB::table('users')
                ->where('id', $adminUserId)
                ->update([
                    'password' => Hash::make('admin'),
                    'updated_at' => $now,
                ]);

            DB::table('voluntarios')->where('user_id', $adminUserId)->delete();
        }

        $finanzasUserId = $finanzasRoleId
            ? DB::table('role_user')->where('role_id', $finanzasRoleId)->value('user_id')
            : DB::table('users')->where('rut', '44.444.444-4')->value('id');

        if ($finanzasUserId) {
            DB::table('voluntarios')->updateOrInsert(
                ['user_id' => $finanzasUserId],
                [
                    'fecha_ingreso' => '2020-01-01',
                    'n_registro' => '90002',
                    'factor_rh' => '+',
                    'grupo_sanguineo' => 'O',
                    'fecha_nacimiento' => '1990-01-01',
                    'created_at' => $now,
                    'updated_at' => $now,
                ]
            );
        }

        DB::table('users')
            ->where('rut', '<>', 'admin')
            ->update([
                'password' => Hash::make('cruzroja26'),
                'updated_at' => $now,
            ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // This migration only normalizes active credentials for the current product rules.
    }
};
