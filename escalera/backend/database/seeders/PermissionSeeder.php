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
                'nombre' => 'Gestionar voluntarios',
                'clave' => 'gestionar_voluntarios',
            ],
            [
                'nombre' => 'Ver historial de voluntarios',
                'clave' => 'ver_historial_voluntarios',
            ],
            [
                'nombre' => 'Gestionar actividades',
                'clave' => 'gestionar_actividades',
            ],
            [
                'nombre' => 'Gestionar actas y analisis',
                'clave' => 'gestionar_actas_analisis',
            ],
            [
                'nombre' => 'Ver reportes de boletas',
                'clave' => 'ver_reportes_boletas',
            ],
            [
                'nombre' => 'Gestionar roles y permisos',
                'clave' => 'gestionar_roles_permisos',
            ],
        ];

        foreach ($permissions as $permission) {
            Permission::updateOrCreate(
                ['clave' => $permission['clave']],
                $permission
            );
        }
    }
}
