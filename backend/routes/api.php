<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\AntecedenteVoluntarioController;
use App\Http\Controllers\HojaAnualController;
use App\Http\Controllers\HojaDeVidaController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\ActivoController;
use App\Http\Controllers\EventoController;
use App\Http\Controllers\ActividadController;
use App\Http\Controllers\RegistroHoraFilialController;
use App\Http\Controllers\VoluntarioController;
//Route::get('/user', function (Request $request) {
//    return $request->user();
//})->middleware('auth:sanctum');

Route::get('user/search', [UserController::class, 'search']);
Route::post('user/{user}/foto-perfil', [UserController::class, 'updateVolunteerPhoto']);
Route::apiResource('user', UserController::class);
Route::apiResource('role', RoleController::class);
Route::apiResource('permissions', PermissionController::class);
Route::apiResource('activo', ActivoController::class);
Route::apiResource('evento', EventoController::class);
Route::apiResource('actividad', ActividadController::class);
Route::apiResource('registros-horas-filial', RegistroHoraFilialController::class);
Route::apiResource('voluntarios', VoluntarioController::class);
Route::apiResource('hojas-de-vida', HojaDeVidaController::class);
Route::apiResource('hojas-anuales', HojaAnualController::class);
Route::apiResource('antecedentes-voluntarios', AntecedenteVoluntarioController::class);
Route::get('/users', function(){
  return App\Models\User::select('id','nombre')->get();
});
