<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\AuthController;
use App\Http\Controllers\FilialController;
use App\Http\Controllers\HojaVidaAnualController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\ActividadController;
use App\Http\Controllers\VoluntarioController;
//Route::get('/user', function (Request $request) {
//    return $request->user();
//})->middleware('auth:sanctum');

Route::post('login', [AuthController::class, 'login']);
Route::get('user/search', [UserController::class, 'search']);
Route::post('user/{user}/foto-perfil', [UserController::class, 'updateVolunteerPhoto']);
Route::apiResource('user', UserController::class);
Route::apiResource('role', RoleController::class);
Route::apiResource('permissions', PermissionController::class);
Route::get('filiales', [FilialController::class, 'index']);
Route::post('actividad/{id}/voluntarios', [ActividadController::class, 'asociarVoluntario']);
Route::delete('actividad/{id}/voluntarios', [ActividadController::class, 'desasociarVoluntario']);
Route::get('actividad/{id}/galeria', [ActividadController::class, 'galeria']);
Route::post('actividad/{id}/galeria', [ActividadController::class, 'subirImagenGaleria']);
Route::get('actividad/{id}/boletas', [ActividadController::class, 'boletas']);
Route::post('actividad/{id}/boletas', [ActividadController::class, 'subirBoleta']);
Route::apiResource('actividad', ActividadController::class);
Route::apiResource('voluntarios', VoluntarioController::class);
Route::get('voluntarios/{voluntario}/hoja-vida-anual', [HojaVidaAnualController::class, 'index']);
Route::post('voluntarios/{voluntario}/hoja-vida-anual', [HojaVidaAnualController::class, 'store']);
Route::get('hoja-vida-anual/{hojaVidaAnual}', [HojaVidaAnualController::class, 'show']);
Route::put('hoja-vida-anual/{hojaVidaAnual}', [HojaVidaAnualController::class, 'update']);
Route::delete('hoja-vida-anual/{hojaVidaAnual}', [HojaVidaAnualController::class, 'destroy']);
Route::get('/users', function(){
  return App\Models\User::with('voluntario')->get()->map(fn ($user) => [
      'id' => $user->id,
      'name' => $user->name,
  ]);
});
