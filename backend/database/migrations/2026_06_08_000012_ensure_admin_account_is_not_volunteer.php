<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

return new class extends Migration
{
    public function up(): void
    {
        $now = now();
        $adminRoleId = DB::table('roles')->where('slug', 'administrador')->value('id');

        DB::table('users')->updateOrInsert(
            ['rut' => 'admin'],
            [
                'nombre' => 'Administrador',
                'email' => 'admin@cruzroja.local',
                'telefono' => 'admin',
                'estado' => 'ACTIVO',
                'password' => Hash::make('admin'),
                'created_at' => $now,
                'updated_at' => $now,
            ]
        );

        $adminUserId = DB::table('users')->where('rut', 'admin')->value('id');

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
            DB::table('voluntarios')->where('user_id', $adminUserId)->delete();
        }
    }

    public function down(): void
    {
        // Intentionally left empty because this migration enforces current access rules.
    }
};
