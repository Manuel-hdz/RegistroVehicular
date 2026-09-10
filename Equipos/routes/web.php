<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\EmpleadoController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    // Si ya hay sesion, entra al panel; si no, manda al login.
    if (! auth()->check()) {
        return redirect()->route('login');
    }

    return auth()->user()->isAdministrador()
        ? redirect()->route('empleados.index')
        : redirect()->route('detector-qr');
});

// Rutas agregadas para iniciar y cerrar sesion.
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.store');
});

Route::get('/registrarse', [AuthController::class, 'showRegister'])->name('register');
Route::post('/registrarse', [AuthController::class, 'register'])->name('register.store');

Route::post('/logout', [AuthController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');

Route::middleware('auth')->group(function () {
    // Todo lo de este grupo requiere usuario autenticado.
    Route::view('/detector-qr', 'qr.detector')->name('detector-qr');
    Route::get('/creadores/coca-cola', [EmpleadoController::class, 'creadores'])->name('creadores.show');
    Route::get('/creadores/coca-cola/edit', [EmpleadoController::class, 'editarCreadores'])->name('creadores.edit');
    Route::match(['put', 'patch'], '/creadores/coca-cola', [EmpleadoController::class, 'actualizarCreadores'])->name('creadores.update');
});

Route::middleware(['auth', 'role:administrador'])->group(function () {
    // Administradores: consultar, agregar y editar unidades/documentos.
    Route::get('unidades/carga-masiva', [EmpleadoController::class, 'cargaMasiva'])
        ->name('empleados.carga-masiva');
    Route::post('unidades/carga-masiva', [EmpleadoController::class, 'guardarCargaMasiva'])
        ->name('empleados.carga-masiva.store');
    Route::get('unidades/fotos-masivas', [EmpleadoController::class, 'fotosMasivas'])
        ->name('empleados.fotos-masivas');
    Route::post('unidades/fotos-masivas', [EmpleadoController::class, 'guardarFotosMasivas'])
        ->name('empleados.fotos-masivas.store');
    Route::get('unidades/importar-csv', [EmpleadoController::class, 'importarCsv'])
        ->name('empleados.importar-csv');
    Route::post('unidades/importar-csv', [EmpleadoController::class, 'guardarImportacionCsv'])
        ->name('empleados.importar-csv.store');
    Route::get('unidades/importar-csv/plantilla', [EmpleadoController::class, 'descargarPlantillaCsv'])
        ->name('empleados.importar-csv.plantilla');
    Route::get('unidades/catalogo-qr', [EmpleadoController::class, 'catalogoQr'])
        ->name('empleados.catalogo-qr');
    Route::post('unidades/catalogo-qr/descargar', [EmpleadoController::class, 'descargarQrSeleccionados'])
        ->name('empleados.catalogo-qr.download');
    Route::get('unidades/{empleado}/qr-descargar', [EmpleadoController::class, 'descargarQr'])
        ->name('empleados.qr.download');

    Route::get('unidades', [EmpleadoController::class, 'index'])->name('empleados.index');
    Route::get('unidades/create', [EmpleadoController::class, 'create'])->name('empleados.create');
    Route::post('unidades', [EmpleadoController::class, 'store'])->name('empleados.store');
    Route::get('unidades/{empleado}/edit', [EmpleadoController::class, 'edit'])->name('empleados.edit');
    Route::match(['put', 'patch'], 'unidades/{empleado}', [EmpleadoController::class, 'update'])->name('empleados.update');
    Route::delete('unidades/{empleado}/foto', [EmpleadoController::class, 'eliminarFoto'])
        ->name('empleados.foto.destroy');
});

Route::middleware(['auth', 'role:superadministrador'])->group(function () {
    // Superusuario: acceso total a administracion.
    Route::delete('unidades', [EmpleadoController::class, 'destroyAll'])
        ->name('empleados.destroy-all');
    Route::delete('unidades/{empleado}', [EmpleadoController::class, 'destroy'])->name('empleados.destroy');
});

Route::middleware('auth')->group(function () {
    // Consulta de unidades: usuarios normales llegan aqui al escanear QR.
    Route::post('unidades/{empleado}/horometro/iniciar', [EmpleadoController::class, 'iniciarHorometro'])
        ->name('empleados.horometro.iniciar');
    Route::post('unidades/{empleado}/horometro/detener', [EmpleadoController::class, 'detenerHorometro'])
        ->name('empleados.horometro.detener');
    Route::post('unidades/{empleado}/horometro/reiniciar', [EmpleadoController::class, 'reiniciarHorometro'])
        ->name('empleados.horometro.reiniciar');
    Route::post('unidades/{empleado}/odometro', [EmpleadoController::class, 'guardarOdometro'])
        ->name('empleados.odometro.store');
    Route::post('unidades/{empleado}/servicio', [EmpleadoController::class, 'registrarServicio'])
        ->name('empleados.servicio.store');
    Route::get('unidades/{empleado}/foto', [EmpleadoController::class, 'verFoto'])
        ->name('empleados.foto');
    Route::get('unidades/{empleado}/pdf/{tipo}', [EmpleadoController::class, 'verPdf'])
        ->name('empleados.pdf');
    Route::get('unidades/{empleado}', [EmpleadoController::class, 'show'])
        ->name('empleados.show');

    // Compatibilidad con enlaces o QR generados antes del cambio de nombre.
    Route::redirect('empleados', 'unidades');
    Route::redirect('empleados/carga-masiva', 'unidades/carga-masiva');
    Route::redirect('empleados/fotos-masivas', 'unidades/fotos-masivas');
    Route::redirect('empleados/importar-csv', 'unidades/importar-csv');
    Route::get('empleados/{empleado}/foto', fn (string $empleado) => redirect()->route('empleados.foto', $empleado));
    Route::get('empleados/{empleado}/pdf/{tipo}', fn (string $empleado, string $tipo) => redirect()->route('empleados.pdf', [$empleado, $tipo]));
    Route::get('empleados/{empleado}', fn (string $empleado) => redirect()->route('empleados.show', $empleado));
});

