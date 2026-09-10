<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

use App\Http\Controllers\Api\EmpleadoApiController;

// Se nombran internamente como api.empleados.* para no romper llamadas existentes del codigo.
Route::apiResource('unidades', EmpleadoApiController::class)
    ->only(['index', 'store', 'show', 'update'])
    ->parameters(['unidades' => 'empleado'])
    ->middleware(['auth:sanctum', 'role:administrador'])
    ->names('api.empleados');

Route::apiResource('unidades', EmpleadoApiController::class)
    ->only(['destroy'])
    ->parameters(['unidades' => 'empleado'])
    ->middleware(['auth:sanctum', 'role:superadministrador'])
    ->names('api.empleados');

// Si deseas exponer las rutas sin autenticación (solo para pruebas), usa:
// Route::apiResource('unidades', EmpleadoApiController::class);
