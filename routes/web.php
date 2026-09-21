<?php

use App\Http\Controllers\historialController;
use App\Http\Controllers\ProveedorController;
use App\Models\Proveedor;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProductoController;
use App\Http\Controllers\PosController;
use App\Http\Controllers\Auth\Login;
use App\Http\Controllers\Auth\Logout;

use App\Http\Middleware\CheckRole;


// Rutas de acceso (públicas para no logueados)
Route::view('/login', 'auth.login')->middleware('guest')->name('login');
Route::post('/login', Login::class)->middleware('guest');

// Rutas del sistema (protegidas por sesión)
Route::middleware('auth')->group(function () {
    Route::get('/', function () {
        return view('dashboard.index');
    });
    Route::get('/dashboard', function () {
        return view('dashboard.index');
    });

    Route::get('/reportes', function () {
        return view('reportes.index');
    });

    // Rutas de Inventario
    Route::get('/inventario', [ProductoController::class, 'listarProductos'])->name('inventario.index');

    Route::get('/inventario/{codigo}', [ProductoController::class, 'show'])->name('inventario.show');

    //Rutas del pos
    Route::get('/pos', [PosController::class, 'index'])->name('pos.index');

    //Rutas del historial

    Route::get('/historial', [historialController::class, 'index'])->name('historial.index');

    // Cierre de sesión
    Route::post('/logout    ', Logout::class)->name('logout');
});

// Rutas específicas protegidas por autenticación y rol de Administrador
Route::middleware(['auth', CheckRole::class . ':Administrador'])->group(function () {
    Route::post('/productos/guardar', [ProductoController::class, 'crearProducto'])->name('productos.crearProducto');
});