<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\AuthController;
use App\Http\Controllers\FilialController;
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
Route::apiResource('actividad', ActividadController::class);
Route::apiResource('voluntarios', VoluntarioController::class);
Route::get('/users', function(){
  return App\Models\User::select('id','name')->get();
});
