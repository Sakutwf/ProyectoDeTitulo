<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\UserController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\ActivoController;
use App\Http\Controllers\EventoController;
use App\Http\Controllers\ActividadController;
//Route::get('/user', function (Request $request) {
//    return $request->user();
//})->middleware('auth:sanctum');

Route::apiResource('user', UserController::class);
Route::apiResource('role', ActivoController::class);
Route::apiResource('activo', ActivoController::class);
Route::apiResource('evento', EventoController::class);
Route::apiResource('actividad', ActividadController::class);
