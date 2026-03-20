<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\BulkImportController;
use App\Http\Controllers\CardexImportController;
use App\Http\Controllers\CardexController;
use App\Http\Controllers\ComedorController;
use App\Http\Controllers\CostCenterController;
use App\Http\Controllers\DepartureController;
use App\Http\Controllers\DriverController;
use App\Http\Controllers\DriverDestroyController;
use App\Http\Controllers\GraphController;
use App\Http\Controllers\HumanResourcesController;
use App\Http\Controllers\MovementController;
use App\Http\Controllers\PersonnelController;
use App\Http\Controllers\PublicDashboardController;
use App\Http\Controllers\RequisitionController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\VacationPolicyController;
use App\Http\Controllers\VehicleController;
use App\Http\Controllers\VehicleDestroyController;
use Illuminate\Support\Facades\Route;

Route::middleware('impersonation-preview')->group(function () {
Route::view('/', 'landing');
Route::get('/comedor', [ComedorController::class, 'index'])->name('comedor.index');
Route::post('/comedor', [ComedorController::class, 'store'])->name('comedor.store');
Route::get('/requisitar', [RequisitionController::class, 'create'])->name('requisitions.create');
Route::post('/requisitar', [RequisitionController::class, 'store'])->name('requisitions.store');

Route::prefix('registroVehicular')->group(function () {
    Route::redirect('/', '/registroVehicular/dashboard');

    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.post');
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    Route::middleware(['auth', \App\Http\Middleware\SingleSession::class])->group(function () {
        Route::get('/movements', [MovementController::class, 'index'])->name('movements.index');
        Route::get('/movements/create', [MovementController::class, 'create'])->name('movements.create');
        Route::post('/movements', [MovementController::class, 'store'])->name('movements.store');
        Route::get('/movements/{movement}/checkin', [MovementController::class, 'checkinForm'])->name('movements.checkin.form');
        Route::put('/movements/{movement}/checkin', [MovementController::class, 'checkin'])->name('movements.checkin');

        Route::middleware('role:admin')->group(function () {
            Route::get('/departures', [DepartureController::class, 'index'])->name('departures.index');
            Route::get('/departures/export', [DepartureController::class, 'export'])->name('departures.export');
            Route::get('/departures/export-excel', [DepartureController::class, 'exportExcel'])->name('departures.export.excel');
        });

        Route::middleware('role:superadmin')->group(function () {
            Route::get('/movements/{movement}/edit', [MovementController::class, 'edit'])->name('movements.edit');
            Route::put('/movements/{movement}', [MovementController::class, 'update'])->name('movements.update');
            Route::put('/movements/{movement}/cancel', [MovementController::class, 'cancel'])->name('movements.cancel');
        });
    });

    Route::get('/dashboard', [PublicDashboardController::class, 'index'])->name('public.dashboard');
    Route::get('/dashboard/graph/by-day.png', [GraphController::class, 'byDay'])->name('dashboard.graph.byday');
    Route::get('/dashboard/graph/top-vehicles.png', [GraphController::class, 'topVehiclesDepartures'])->name('dashboard.graph.topvehicles');
    Route::get('/dashboard/graph/top-km.png', [GraphController::class, 'topVehiclesKm'])->name('dashboard.graph.topkm');
    Route::get('/dashboard/graph/top-drivers.png', [GraphController::class, 'topDriversDepartures'])->name('dashboard.graph.topdrivers');
    Route::get('/dashboard/graph/status', [GraphController::class, 'status'])->name('dashboard.graph.status');
});
});

Route::middleware(['auth', \App\Http\Middleware\SingleSession::class, 'impersonation-preview'])->group(function () {
    Route::post('/vista-usuario', [UserController::class, 'startPreview'])->name('impersonation.start');
    Route::delete('/vista-usuario', [UserController::class, 'stopPreview'])->name('impersonation.stop');

    Route::prefix('mantenimiento')->middleware('section:mantenimiento')->group(function () {
        Route::get('/', [\App\Http\Controllers\MaintenanceController::class, 'index'])->name('maintenance.index');
        Route::put('/vehiculos/{vehicle}', [\App\Http\Controllers\MaintenanceController::class, 'update'])->name('maintenance.update');
        Route::resource('mecanicos', \App\Http\Controllers\MechanicController::class)
            ->parameters(['mecanicos' => 'mechanic'])
            ->names('mechanics')
            ->only(['index', 'create', 'store', 'edit', 'update']);
        Route::resource('reparaciones', \App\Http\Controllers\RepairController::class)
            ->parameters(['reparaciones' => 'repair'])
            ->names('repairs')
            ->only(['index', 'create', 'store']);
    });

    Route::prefix('administracion')->group(function () {
        Route::middleware('section:administracion')->group(function () {
            Route::get('/vehiculos/{vehicle}/documentos/{document}', [VehicleController::class, 'document'])->name('vehicles.document');
            Route::resource('vehiculos', VehicleController::class)
                ->parameters(['vehiculos' => 'vehicle'])
                ->names('vehicles')
                ->only(['index', 'create', 'store', 'edit', 'update']);
        });

        Route::middleware(['role:superadmin', 'section:administracion'])->group(function () {
            Route::delete('/vehiculos/{vehicle}', VehicleDestroyController::class)->name('vehicles.destroy');
        });
    });

    Route::prefix('configuracion')->middleware('section:configuracion')->group(function () {
        Route::resource('centros-costos', CostCenterController::class)
            ->parameters(['centros-costos' => 'costCenter'])
            ->names('cost-centers')
            ->only(['index', 'create', 'store', 'edit', 'update']);
        Route::get('/cargas-masivas', [BulkImportController::class, 'index'])->name('bulk-imports.index');
        Route::get('/cargas-masivas/plantillas/{type}', [BulkImportController::class, 'template'])->name('bulk-imports.template');
        Route::post('/cargas-masivas/{type}', [BulkImportController::class, 'store'])->name('bulk-imports.store');
        Route::get('/tabla-vacaciones', [VacationPolicyController::class, 'index'])->name('vacation-policies.index');
        Route::put('/tabla-vacaciones', [VacationPolicyController::class, 'updateTable'])->name('vacation-policies.update-table');
        Route::middleware('role:superadmin')->group(function () {
            Route::resource('usuarios', UserController::class)
                ->parameters(['usuarios' => 'user'])
                ->names('users')
                ->only(['index', 'create', 'store', 'edit', 'update']);
            Route::patch('/usuarios/{user}/permisos', [UserController::class, 'updatePermissions'])->name('users.permissions');
        });
    });

    Route::prefix('rrhh')->group(function () {
        Route::middleware('section:rrhh')->group(function () {
            Route::get('/', [HumanResourcesController::class, 'index'])->name('hr.index');
            Route::resource('personal', PersonnelController::class)
                ->parameters(['personal' => 'personnel'])
                ->names('personnel')
                ->only(['index', 'create', 'store', 'edit', 'update']);
            Route::get('/personal/{personnel}/foto', [PersonnelController::class, 'photo'])->name('personnel.photo');
            Route::patch('/personal/{personnel}/deactivate', [PersonnelController::class, 'deactivate'])->name('personnel.deactivate');
            Route::patch('/personal/{personnel}/reactivate', [PersonnelController::class, 'reactivate'])->name('personnel.reactivate');
            Route::get('/cardex', [CardexController::class, 'index'])->name('cardex.index');
            Route::post('/cardex', [CardexController::class, 'store'])->name('cardex.store');
            Route::get('/cardex/importar', [CardexImportController::class, 'index'])->name('cardex.import.index');
            Route::get('/cardex/importar/plantilla', [CardexImportController::class, 'template'])->name('cardex.import.template');
            Route::post('/cardex/importar', [CardexImportController::class, 'store'])->name('cardex.import.store');
            Route::resource('conductores', DriverController::class)
                ->parameters(['conductores' => 'driver'])
                ->names('drivers')
                ->only(['index', 'create', 'store', 'edit', 'update']);
            Route::get('/registrosComedor', [ComedorController::class, 'records'])->name('comedor.records');
        });

        Route::middleware(['role:superadmin', 'section:rrhh'])->group(function () {
            Route::delete('/conductores/{driver}', DriverDestroyController::class)->name('drivers.destroy');
            Route::delete('/personal/{personnel}', [PersonnelController::class, 'destroy'])->name('personnel.destroy');
        });
    });

    Route::prefix('almacen')->group(function () {
        Route::middleware('section:refacciones')->group(function () {
            Route::get('/refacciones', [\App\Http\Controllers\PartController::class, 'index'])->name('parts.index');
        });

        Route::middleware(['section:almacen', 'module-owner:almacen'])->group(function () {
            Route::get('/refacciones/create', [\App\Http\Controllers\PartController::class, 'create'])->name('parts.create');
            Route::post('/refacciones', [\App\Http\Controllers\PartController::class, 'store'])->name('parts.store');
            Route::get('/refacciones/{part}/edit', [\App\Http\Controllers\PartController::class, 'edit'])->name('parts.edit');
            Route::put('/refacciones/{part}', [\App\Http\Controllers\PartController::class, 'update'])->name('parts.update');
        });
    });

    Route::prefix('compras')->group(function () {
        Route::middleware('section:pendientes')->group(function () {
            Route::get('/pendientes', [RequisitionController::class, 'pending'])->name('requisitions.pending');
        });

        Route::middleware(['section:compras', 'module-owner:compras'])->group(function () {
            Route::patch('/requisiciones/{requisition}/estatus', [RequisitionController::class, 'updateStatus'])->name('requisitions.status');
        });

        Route::middleware(['section:almacen', 'module-owner:almacen'])->group(function () {
            Route::patch('/requisiciones/materiales/{requisitionItem}/checks', [RequisitionController::class, 'updateItemChecks'])->name('requisitions.items.checks');
        });
    });
});
