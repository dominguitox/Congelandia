<?php

use App\Http\Controllers\historialController;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\ProductoController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PosController;
use App\Http\Controllers\ClienteController;
use App\Http\Controllers\ProveedorController;

use App\Http\Controllers\Auth\Login;
use App\Http\Controllers\Auth\Logout;

use App\Http\Middleware\CheckRole;


// =========================================================
// RUTAS DE ACCESO
// =========================================================

// Login
Route::view('/login', 'auth.login')
    ->middleware('guest')
    ->name('login');

Route::post('/login', Login::class)
    ->middleware('guest');


// =========================================================
// RUTAS DEL SISTEMA
// =========================================================

Route::middleware('auth')->group(function () {


    // =====================================================
    // DASHBOARD
    // =====================================================

    Route::get('/', function () {
        return view('dashboard.index');
    });

    Route::get('/dashboard', function () {
        return view('dashboard.index');
    });

    Route::get(
        '/dashboard',
        [DashboardController::class, 'index']
    )->name('dashboard.index');


    // =====================================================
    // REPORTES
    // =====================================================

    Route::get('/reportes', function () {
        return view('reportes.index');
    });


    // =====================================================
    // INVENTARIO
    // =====================================================

    Route::get(
        '/inventario',
        [ProductoController::class, 'listarProductos']
    )->name('inventario.index');

    Route::get(
        '/inventario/{codigo}',
        [ProductoController::class, 'show']
    )->name('inventario.show');

    Route::put(
        '/productos/{codigo}',
        [ProductoController::class, 'editarProducto']
    )->name('inventario.editarProducto');


    // =====================================================
    // POS
    // =====================================================

    Route::get(
        '/pos',
        [PosController::class, 'index']
    )->name('pos.index');

    Route::post(
        '/venta/registrar',
        [PosController::class, 'registrarVenta']
    )->name('venta.registrar');


    // =====================================================
    // HISTORIAL GENERAL
    // =====================================================

    Route::get(
        '/historial',
        [historialController::class, 'index']
    )->name('historial.index');


    // =====================================================
    // CLIENTES
    // =====================================================

    // Página principal
    Route::get(
        '/clientes',
        [ClienteController::class, 'index']
    )->name('clientes.index');


    // Crear cliente
    Route::post(
        '/clientes',
        [ClienteController::class, 'store']
    )->name('clientes.store');


    // Historial de un cliente
    Route::get(
        '/clientes/{rutCliente}/historial',
        [ClienteController::class, 'historial']
    )->name('clientes.historial');


    // Registrar pago de una venta
    Route::post(
        '/clientes/venta/{idSalida}/pago',
        [ClienteController::class, 'registrarPago']
    )->name('clientes.registrarPago');


    // Editar cliente
    Route::put(
        '/clientes/{rutCliente}',
        [ClienteController::class, 'update']
    )->name('clientes.update');


    // Eliminar cliente
    Route::delete(
        '/clientes/{rutCliente}',
        [ClienteController::class, 'destroy']
    )->name('clientes.destroy');


    // =====================================================
    // PROVEEDORES
    // =====================================================

    // Página principal de proveedores
    Route::get(
        '/proveedores',
        [ProveedorController::class, 'listarProveedores']
    )->name('proveedores.index');


    // Crear proveedor
    Route::post(
        '/proveedores',
        [ProveedorController::class, 'store']
    )->name('proveedores.store');


    // Mostrar productos asociados a un proveedor
    Route::get(
        '/proveedores/{idProveedor}/productos',
        [ProveedorController::class, 'productos']
    )->name('proveedores.productos');


    // Editar proveedor
    Route::put(
        '/proveedores/{idProveedor}',
        [ProveedorController::class, 'update']
    )->name('proveedores.update');


    // Eliminar proveedor
    Route::delete(
        '/proveedores/{idProveedor}',
        [ProveedorController::class, 'destroy']
    )->name('proveedores.destroy');


    // =====================================================
    // CIERRE DE SESIÓN
    // =====================================================

    Route::post(
        '/logout',
        Logout::class
    )->name('logout');
});


// =========================================================
// RUTAS ADMINISTRADOR
// =========================================================

Route::middleware([
    'auth',
    CheckRole::class . ':Administrador'
])->group(function () {

    Route::post(
        '/productos/guardar',
        [ProductoController::class, 'crearProducto']
    )->name('productos.crearProducto');

});