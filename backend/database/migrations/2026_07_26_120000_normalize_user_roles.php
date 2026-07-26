<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $now = now();
        $roleNames = [
            'administrador' => 'Administrador',
            'voluntario' => 'Voluntario',
            'moderador' => 'Moderador',
        ];

        foreach ($roleNames as $key => $name) {
            DB::table('roles')->updateOrInsert(
                ['clave' => $key],
                ['nombre' => $name, 'updated_at' => $now, 'created_at' => $now]
            );
        }

        $roleIds = DB::table('roles')
            ->whereIn('clave', array_keys($roleNames))
            ->pluck('id', 'clave');

        $allPermissionIds = DB::table('permissions')->pluck('id');
        $volunteerPermissionIds = DB::table('permissions')
            ->where('clave', 'ver_historial_voluntarios')
            ->pluck('id');

        DB::table('permission_role')
            ->whereIn('role_id', $roleIds->values())
            ->delete();

        foreach (['administrador', 'moderador'] as $roleKey) {
            foreach ($allPermissionIds as $permissionId) {
                DB::table('permission_role')->insert([
                    'permission_id' => $permissionId,
                    'role_id' => $roleIds[$roleKey],
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }

        foreach ($volunteerPermissionIds as $permissionId) {
            DB::table('permission_role')->insert([
                'permission_id' => $permissionId,
                'role_id' => $roleIds['voluntario'],
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        foreach (DB::table('users')->pluck('id') as $userId) {
            $volunteerId = DB::table('voluntarios')
                ->where('user_id', $userId)
                ->value('id');

            if (! $volunteerId) {
                $roleKey = 'administrador';
            } else {
                $hasCurrentCargo = DB::table('hoja_vida_anual')
                    ->where('voluntario_id', $volunteerId)
                    ->where('anio', (int) now()->year)
                    ->whereNotNull('cargo_clave')
                    ->where('cargo_clave', '<>', '')
                    ->exists();

                $roleKey = $hasCurrentCargo ? 'moderador' : 'voluntario';
            }

            DB::table('role_user')->where('user_id', $userId)->delete();
            DB::table('role_user')->insert([
                'user_id' => $userId,
                'role_id' => $roleIds[$roleKey],
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        DB::table('roles')
            ->whereNotIn('clave', array_keys($roleNames))
            ->delete();
    }

    public function down(): void
    {
        // La separación entre roles y cargos no debe revertirse automáticamente.
    }
};
