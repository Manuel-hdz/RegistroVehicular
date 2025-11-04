<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MovementController;
use App\Http\Controllers\VehicleController;
use App\Http\Controllers\DriverController;
use App\Http\Controllers\AuthController;

Route::redirect('/', '/movements');

// Sin autenticación: todo público temporalmente
// Movements
Route::get('/movements', [MovementController::class, 'index'])->name('movements.index');
Route::get('/movements/create', [MovementController::class, 'create'])->name('movements.create');
Route::post('/movements', [MovementController::class, 'store'])->name('movements.store');
Route::get('/movements/{movement}/checkin', [MovementController::class, 'checkinForm'])->name('movements.checkin.form');
Route::put('/movements/{movement}/checkin', [MovementController::class, 'checkin'])->name('movements.checkin');

// Catálogos públicos (solo las acciones implementadas)
Route::resource('vehicles', VehicleController::class)->only(['index','create','store','edit','update']);
Route::resource('drivers', DriverController::class)->only(['index','create','store','edit','update']);
