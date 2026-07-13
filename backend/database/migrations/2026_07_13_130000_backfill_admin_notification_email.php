<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $contactEmail = DB::table('portada_ajustes')->where('id', 1)->value('correo_contacto');

        if (! filter_var($contactEmail, FILTER_VALIDATE_EMAIL)) {
            return;
        }

        $reviewerIds = DB::table('role_user')
            ->join('roles', 'roles.id', '=', 'role_user.role_id')
            ->whereIn('roles.clave', ['administrador', 'secretario-directiva'])
            ->pluck('role_user.user_id');

        DB::table('users')
            ->whereIn('id', $reviewerIds)
            ->whereNull('correo_notificaciones')
            ->update(['correo_notificaciones' => $contactEmail]);
    }

    public function down(): void {}
};
