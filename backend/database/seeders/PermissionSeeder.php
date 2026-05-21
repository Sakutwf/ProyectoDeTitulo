<?php

namespace Database\Seeders;

use App\Models\Permission;
use Illuminate\Database\Seeder;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permissions = [
            [
                'name' => 'Gestionar voluntarios',
                'slug' => 'gestionar_voluntarios',
                'description' => 'Permite crear, editar y eliminar voluntarios.',
            ],
            [
                'name' => 'Ver historial de voluntarios',
                'slug' => 'ver_historial_voluntarios',
                'description' => 'Permite revisar hojas de vida, hojas anuales y antecedentes.',
            ],
            [
                'name' => 'Gestionar actividades',
                'slug' => 'gestionar_actividades',
                'description' => 'Permite administrar el CRUD de actividades.',
            ],
            [
                'name' => 'Gestionar eventos',
                'slug' => 'gestionar_eventos',
                'description' => 'Permite administrar el CRUD de eventos.',
            ],
            [
                'name' => 'Ver reportes de boletas',
                'slug' => 'ver_reportes_boletas',
                'description' => 'Permite acceder a reportes y pagos asociados a boletas.',
            ],
            [
                'name' => 'Gestionar roles y permisos',
                'slug' => 'gestionar_roles_permisos',
                'description' => 'Permite administrar la matriz de acceso del sistema.',
            ],
        ];

        foreach ($permissions as $permission) {
            Permission::updateOrCreate(
                ['slug' => $permission['slug']],
                $permission
            );
        }
    }
}
