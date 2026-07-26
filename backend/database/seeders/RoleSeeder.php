<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = [
            [
                'nombre' => 'Administrador',
                'clave' => 'administrador',
                'permissions' => Permission::pluck('id')->all(),
            ],
            [
                'nombre' => 'Voluntario',
                'clave' => 'voluntario',
                'permissions' => [
                    Permission::where('clave', 'ver_historial_voluntarios')->value('id'),
                ],
            ],
            [
                'nombre' => 'Moderador',
                'clave' => 'moderador',
                'permissions' => Permission::pluck('id')->all(),
            ],
        ];

        foreach ($roles as $roleData) {
            $permissions = array_values(array_filter($roleData['permissions']));
            unset($roleData['permissions']);

            $role = Role::updateOrCreate(
                ['clave' => $roleData['clave']],
                $roleData
            );

            $role->permissions()->sync($permissions);
        }

        Role::query()
            ->whereNotIn('clave', ['administrador', 'voluntario', 'moderador'])
            ->delete();
    }
}
