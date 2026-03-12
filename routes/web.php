<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CardexImportController;
use App\Http\Controllers\CardexController;
use App\Http\Controllers\DepartureController;
use App\Http\Controllers\DriverController;
use App\Http\Controllers\DriverDestroyController;
use App\Http\Controllers\GraphController;
use App\Http\Controllers\HumanResourcesController;
use App\Http\Controllers\MovementController;
use App\Http\Controllers\PersonnelController;
use App\Http\Controllers\PublicDashboardController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\VehicleController;
use App\Http\Controllers\VehicleDestroyController;
use Illuminate\Support\Facades\Route;

Route::view('/', 'landing');

Route::prefix('registroVehicular')->group(function () {
    Route::redirect('/', '/registroVehicular/dashboard');

    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.post');
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    Route::middleware(['auth', \App\Http\Middleware\SingleSession::class])->group(function () {
        Route::middleware('role:superadmin')->group(function () {
            Route::resource('users', UserController::class)->only(['index', 'create', 'store', 'edit', 'update']);

            Route::delete('/vehicles/{vehicle}', VehicleDestroyController::class)->name('vehicles.destroy');
            Route::delete('/drivers/{driver}', DriverDestroyController::class)->name('drivers.destroy');
            Route::delete('/personnel/{personnel}', [PersonnelController::class, 'destroy'])->name('personnel.destroy');

            Route::get('/movements/{movement}/edit', [MovementController::class, 'edit'])->name('movements.edit');
            Route::put('/movements/{movement}', [MovementController::class, 'update'])->name('movements.update');
            Route::put('/movements/{movement}/cancel', [MovementController::class, 'cancel'])->name('movements.cancel');
        });

        Route::middleware('role:admin')->group(function () {
            Route::resource('vehicles', VehicleController::class)->only(['index', 'create', 'store', 'edit', 'update']);
            Route::resource('drivers', DriverController::class)->only(['index', 'create', 'store', 'edit', 'update']);
            Route::resource('personnel', PersonnelController::class)->only(['index', 'create', 'store', 'edit', 'update']);
            Route::patch('/personnel/{personnel}/deactivate', [PersonnelController::class, 'deactivate'])->name('personnel.deactivate');
            Route::patch('/personnel/{personnel}/reactivate', [PersonnelController::class, 'reactivate'])->name('personnel.reactivate');

            Route::get('/human-resources', [HumanResourcesController::class, 'index'])->name('hr.index');
            Route::get('/cardex', [CardexController::class, 'index'])->name('cardex.index');
            Route::post('/cardex', [CardexController::class, 'store'])->name('cardex.store');
            Route::get('/cardex/import', [CardexImportController::class, 'index'])->name('cardex.import.index');
            Route::get('/cardex/import/template', [CardexImportController::class, 'template'])->name('cardex.import.template');
            Route::post('/cardex/import', [CardexImportController::class, 'store'])->name('cardex.import.store');

            Route::get('/departures', [DepartureController::class, 'index'])->name('departures.index');
            Route::get('/departures/export', [DepartureController::class, 'export'])->name('departures.export');
            Route::get('/departures/export-excel', [DepartureController::class, 'exportExcel'])->name('departures.export.excel');

            Route::get('/maintenance', [\App\Http\Controllers\MaintenanceController::class, 'index'])->name('maintenance.index');
            Route::put('/maintenance/{vehicle}', [\App\Http\Controllers\MaintenanceController::class, 'update'])->name('maintenance.update');

            Route::resource('parts', \App\Http\Controllers\PartController::class)->only(['index', 'create', 'store', 'edit', 'update']);
            Route::resource('mechanics', \App\Http\Controllers\MechanicController::class)->only(['index', 'create', 'store', 'edit', 'update']);
            Route::resource('repairs', \App\Http\Controllers\RepairController::class)->only(['index', 'create', 'store']);
        });

        Route::get('/movements', [MovementController::class, 'index'])->name('movements.index');
        Route::get('/movements/create', [MovementController::class, 'create'])->name('movements.create');
        Route::post('/movements', [MovementController::class, 'store'])->name('movements.store');
        Route::get('/movements/{movement}/checkin', [MovementController::class, 'checkinForm'])->name('movements.checkin.form');
        Route::put('/movements/{movement}/checkin', [MovementController::class, 'checkin'])->name('movements.checkin');
    });

    Route::get('/dashboard', [PublicDashboardController::class, 'index'])->name('public.dashboard');
    Route::get('/dashboard/graph/by-day.png', [GraphController::class, 'byDay'])->name('dashboard.graph.byday');
    Route::get('/dashboard/graph/top-vehicles.png', [GraphController::class, 'topVehiclesDepartures'])->name('dashboard.graph.topvehicles');
    Route::get('/dashboard/graph/top-km.png', [GraphController::class, 'topVehiclesKm'])->name('dashboard.graph.topkm');
    Route::get('/dashboard/graph/top-drivers.png', [GraphController::class, 'topDriversDepartures'])->name('dashboard.graph.topdrivers');
    Route::get('/dashboard/graph/status', [GraphController::class, 'status'])->name('dashboard.graph.status');
});
