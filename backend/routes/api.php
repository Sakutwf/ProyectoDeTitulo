<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\FilialController;
use App\Http\Controllers\HojaVidaAnualController;
use App\Http\Controllers\DocumentoActividadController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\ActividadController;
use App\Http\Controllers\AlbumController;
use App\Http\Controllers\VoluntarioController;
use App\Http\Controllers\PortadaController;
use App\Http\Controllers\SolicitudHojaVidaController;

Route::post('login', [AuthController::class, 'login']);
Route::get('portada', [PortadaController::class, 'show']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('portada/opciones', [PortadaController::class, 'options']);
    Route::post('portada/imagenes', [PortadaController::class, 'uploadImages']);
    Route::put('portada/configuracion', [PortadaController::class, 'update']);
    Route::post('logout', [AuthController::class, 'logout']);
    Route::get('me', fn (Request $request) => $request->user()->loadMissing('roles.permissions', 'voluntario'));

    Route::get('user/search', [UserController::class, 'search']);
    Route::post('user/{user}/foto-perfil', [UserController::class, 'updateVolunteerPhoto']);
    Route::apiResource('user', UserController::class);
    Route::apiResource('role', RoleController::class);
    Route::apiResource('permissions', PermissionController::class);
    Route::get('filiales', [FilialController::class, 'index']);

    Route::get('albumes', [AlbumController::class, 'index']);
    Route::post('albumes', [AlbumController::class, 'store']);
    Route::get('albumes/{album}', [AlbumController::class, 'show']);
    Route::put('albumes/{album}', [AlbumController::class, 'update']);
    Route::delete('albumes/{album}', [AlbumController::class, 'destroy']);
    Route::post('albumes/{album}/fotos', [AlbumController::class, 'uploadPhoto']);
    Route::get('albumes/{album}/fotos/{archivo}/descargar', [AlbumController::class, 'downloadPhoto']);
    Route::put('albumes/{album}/fotos/{archivo}', [AlbumController::class, 'updatePhoto']);
    Route::delete('albumes/{album}/fotos/{archivo}', [AlbumController::class, 'destroyPhoto']);

    Route::get('actividad/{actividad}/documentos/prefill', [DocumentoActividadController::class, 'prefill']);
    Route::get('actividad/{actividad}/documentos', [DocumentoActividadController::class, 'index']);
    Route::post('actividad/{actividad}/documentos', [DocumentoActividadController::class, 'store']);
    Route::get('documentos-actividad/{documentoActividad}', [DocumentoActividadController::class, 'show']);
    Route::put('documentos-actividad/{documentoActividad}', [DocumentoActividadController::class, 'update']);
    Route::delete('documentos-actividad/{documentoActividad}', [DocumentoActividadController::class, 'destroy']);

    Route::post('actividad/{id}/voluntarios', [ActividadController::class, 'asociarVoluntario']);
    Route::delete('actividad/{id}/voluntarios', [ActividadController::class, 'desasociarVoluntario']);
    Route::put('actividad/{id}/climas', [ActividadController::class, 'guardarClimas']);
    Route::get('actividad/{id}/galeria', [ActividadController::class, 'galeria']);
    Route::get('actividad/{id}/notificaciones/destinatarios', [ActividadController::class, 'destinatariosNotificacion']);
    Route::post('actividad/{id}/notificar-voluntarios', [ActividadController::class, 'notificarVoluntarios']);
    Route::post('actividad/{id}/galeria', [ActividadController::class, 'subirImagenGaleria']);
    Route::delete('actividad/{id}/galeria-temporal/{archivo}', [ActividadController::class, 'eliminarImagenTemporalClima']);
    Route::get('actividad/{id}/boletas', [ActividadController::class, 'boletas']);
    Route::post('actividad/{id}/boletas', [ActividadController::class, 'subirBoleta']);
    Route::get('boletas/gestion', [ActividadController::class, 'gestionBoletas']);
    Route::put('boletas/{boletaViatico}', [ActividadController::class, 'actualizarBoleta']);
    Route::put('boletas/{boletaViatico}/estado', [ActividadController::class, 'actualizarEstadoBoleta']);
    Route::delete('boletas/{boletaViatico}', [ActividadController::class, 'eliminarBoleta']);
    Route::apiResource('actividad', ActividadController::class);
    Route::apiResource('voluntarios', VoluntarioController::class);

    Route::get('voluntarios/{voluntario}/hoja-vida-anual', [HojaVidaAnualController::class, 'index']);
    Route::post('voluntarios/{voluntario}/hoja-vida-anual', [HojaVidaAnualController::class, 'store']);
    Route::get('hoja-vida-anual/{hojaVidaAnual}', [HojaVidaAnualController::class, 'show']);
    Route::put('hoja-vida-anual/{hojaVidaAnual}', [HojaVidaAnualController::class, 'update']);
    Route::delete('hoja-vida-anual/{hojaVidaAnual}', [HojaVidaAnualController::class, 'destroy']);
    Route::get('solicitudes-hoja-vida', [SolicitudHojaVidaController::class, 'index']);
    Route::get('mis-solicitudes-hoja-vida', [SolicitudHojaVidaController::class, 'mine']);
    Route::put('solicitudes-hoja-vida/{solicitudHojaVida}/revisar', [SolicitudHojaVidaController::class, 'review']);

    Route::get('/users', function () {
        return App\Models\User::with('voluntario')->get()->map(fn ($user) => [
            'id' => $user->id,
            'name' => $user->name,
        ]);
    });
});
