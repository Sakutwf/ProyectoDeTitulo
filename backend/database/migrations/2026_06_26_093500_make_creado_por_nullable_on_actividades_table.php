<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('ALTER TABLE actividades DROP FOREIGN KEY actividades_creado_por_foreign');
        DB::statement('ALTER TABLE actividades MODIFY creado_por BIGINT UNSIGNED NULL');
        DB::statement('ALTER TABLE actividades ADD CONSTRAINT actividades_creado_por_foreign FOREIGN KEY (creado_por) REFERENCES users(id) ON DELETE SET NULL');
    }

    public function down(): void
    {
        $defaultUserId = DB::table('users')->orderBy('id')->value('id');

        if ($defaultUserId === null) {
            throw new RuntimeException('No existe un usuario para restaurar creado_por como obligatorio.');
        }

        DB::table('actividades')->whereNull('creado_por')->update([
            'creado_por' => $defaultUserId,
        ]);

        DB::statement('ALTER TABLE actividades DROP FOREIGN KEY actividades_creado_por_foreign');
        DB::statement('ALTER TABLE actividades MODIFY creado_por BIGINT UNSIGNED NOT NULL');
        DB::statement('ALTER TABLE actividades ADD CONSTRAINT actividades_creado_por_foreign FOREIGN KEY (creado_por) REFERENCES users(id) ON DELETE RESTRICT');
    }
};