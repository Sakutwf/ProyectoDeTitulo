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
                'name' => 'Administrador',
                'slug' => 'administrador',
                'description' => 'Acceso completo a la administracion del sistema.',
                'permissions' => Permission::pluck('id')->all(),
            ],
            [
                'name' => 'Voluntario',
                'slug' => 'voluntario',
                'description' => 'Habilita la ficha y el historial del voluntario.',
                'permissions' => [
                    Permission::where('slug', 'ver_historial_voluntarios')->value('id'),
                ],
            ],
            [
                'name' => 'Secretario Directiva',
                'slug' => 'secretario-directiva',
                'description' => 'Gestiona voluntarios, actividades y eventos.',
                'permissions' => [
                    Permission::where('slug', 'gestionar_voluntarios')->value('id'),
                    Permission::where('slug', 'ver_historial_voluntarios')->value('id'),
                    Permission::where('slug', 'gestionar_actividades')->value('id'),
                    Permission::where('slug', 'gestionar_eventos')->value('id'),
                ],
            ],
            [
                'name' => 'Encargada Finanzas',
                'slug' => 'encargada-finanzas',
                'description' => 'Accede solo a los reportes de boletas.',
                'permissions' => [
                    Permission::where('slug', 'ver_reportes_boletas')->value('id'),
                ],
            ],
        ];

        foreach ($roles as $roleData) {
            $permissions = array_values(array_filter($roleData['permissions']));
            unset($roleData['permissions']);

            $role = Role::updateOrCreate(
                ['slug' => $roleData['slug']],
                $roleData
            );

            $role->permissions()->sync($permissions);
        }
    }
}
