<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MovementController;
use App\Http\Controllers\VehicleController;
use App\Http\Controllers\DriverController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\VehicleDestroyController;
use App\Http\Controllers\DriverDestroyController;
use App\Http\Controllers\DepartureController;
use App\Http\Controllers\PublicDashboardController;

Route::redirect('/', '/dashboard');

// Auth
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Rutas protegidas
Route::middleware('auth')->group(function () {
    // Usuarios (solo SuperAdmin)
    Route::middleware('role:superadmin')->group(function () {
        Route::resource('users', UserController::class)->only(['index','create','store','edit','update']);
        // Destrucción/edición avanzadas
        Route::delete('/vehicles/{vehicle}', VehicleDestroyController::class)->name('vehicles.destroy');
        Route::delete('/drivers/{driver}', DriverDestroyController::class)->name('drivers.destroy');
        Route::get('/movements/{movement}/edit', [MovementController::class, 'edit'])->name('movements.edit');
        Route::put('/movements/{movement}', [MovementController::class, 'update'])->name('movements.update');
        Route::put('/movements/{movement}/cancel', [MovementController::class, 'cancel'])->name('movements.cancel');
    });

    // Administradores y superiores: gestionar catálogos
    Route::middleware('role:admin')->group(function () {
        Route::resource('vehicles', VehicleController::class)->only(['index','create','store','edit','update']);
        Route::resource('drivers', DriverController::class)->only(['index','create','store','edit','update']);
        Route::get('/departures', [DepartureController::class, 'index'])->name('departures.index');
        Route::get('/departures/export', [DepartureController::class, 'export'])->name('departures.export');
    });

    // Usuarios: solo movimientos
    Route::get('/movements', [MovementController::class, 'index'])->name('movements.index');
    Route::get('/movements/create', [MovementController::class, 'create'])->name('movements.create');
    Route::post('/movements', [MovementController::class, 'store'])->name('movements.store');
    Route::get('/movements/{movement}/checkin', [MovementController::class, 'checkinForm'])->name('movements.checkin.form');
    Route::put('/movements/{movement}/checkin', [MovementController::class, 'checkin'])->name('movements.checkin');
});

// Dashboard público (sin autenticación)
Route::get('/dashboard', [PublicDashboardController::class, 'index'])->name('public.dashboard');
