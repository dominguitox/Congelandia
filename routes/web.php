<?php

use App\Http\Controllers\HistorialController;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\ProductoController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PosController;
use App\Http\Controllers\ClienteController;
use App\Http\Controllers\PublicController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ProveedorController;
use App\Http\Controllers\CategoriaController;
use App\Http\Controllers\VersionController;
use App\Http\Controllers\ReporteController;

use App\Http\Controllers\Auth\Login;
use App\Http\Controllers\Auth\Logout;

use App\Http\Middleware\CheckRole;


// =========================================================
// SITIO PÚBLICO
// =========================================================

Route::get(
    '/',
    [PublicController::class, 'index']
)->name('public.index');

Route::get(
    '/catalogo',
    [PublicController::class, 'catalogo']
)->name('public.catalogo');

Route::get(
    '/contacto',
    [PublicController::class, 'contacto']
)->name('public.contacto');


// =========================================================
// RUTAS DE ACCESO
// =========================================================

Route::view(
    '/login',
    'auth.login'
)
    ->middleware('guest')
    ->name('login');

Route::post(
    '/login',
    Login::class
)
    ->middleware('guest');


// =========================================================
// RUTAS DEL SISTEMA
// =========================================================

Route::middleware('auth')->group(function () {


    // =====================================================
    // INFORMACIÓN DE VERSIÓN
    // =====================================================

    Route::get(
        '/version-info',
        [PublicController::class, 'versionInfo']
    );


    // =====================================================
    // DASHBOARD
    // =====================================================

    Route::get(
        '/dashboard',
        [DashboardController::class, 'index']
    )->name('dashboard.index');


    // =====================================================
    // REPORTES
    // =====================================================

    Route::get(
        '/reportes',
        [ReporteController::class, 'index']
    )->name('reportes.index');


    // =====================================================
    // INVENTARIO
    // =====================================================

    Route::get(
        '/inventario',
        [ProductoController::class, 'listarProductos']
    )->name('inventario.index');
    /*
    No se implementó, guardar por si acaso
        Route::get(
            '/inventario/{codigo}',
            [ProductoController::class, 'show']
        )->name('inventario.show');
    */
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
    // HISTORIAL
    // =====================================================

    Route::get(
        '/historial',
        [HistorialController::class, 'index']
    )->name('historial.index');


    // =====================================================
    // CATEGORÍAS
    // =====================================================

    Route::get(
        '/categorias',
        [CategoriaController::class, 'index']
    )->name('categorias.index');

    Route::post(
        '/categorias',
        [CategoriaController::class, 'store']
    )->name('categorias.store');

    Route::put(
        '/categorias/{idCategoria}',
        [CategoriaController::class, 'update']
    )->name('categorias.update');

    Route::delete(
        '/categorias/{idCategoria}',
        [CategoriaController::class, 'destroy']
    )->name('categorias.destroy');


    // =====================================================
    // CLIENTES
    // =====================================================

    Route::get(
        '/clientes',
        [ClienteController::class, 'index']
    )->name('clientes.index');

    Route::post(
        '/clientes',
        [ClienteController::class, 'store']
    )->name('clientes.store');

    Route::get(
        '/clientes/{rutCliente}/historial',
        [ClienteController::class, 'historial']
    )->name('clientes.historial');

    Route::post(
        '/clientes/venta/{idSalida}/pago',
        [ClienteController::class, 'registrarPago']
    )->name('clientes.registrarPago');

    Route::put(
        '/clientes/{rutCliente}',
        [ClienteController::class, 'update']
    )->name('clientes.update');

    Route::delete(
        '/clientes/{rutCliente}',
        [ClienteController::class, 'destroy']
    )->name('clientes.destroy');


    // =====================================================
    // PROVEEDORES
    // =====================================================

    Route::get(
        '/proveedores',
        [ProveedorController::class, 'listarProveedores']
    )->name('proveedores.index');

    Route::post(
        '/proveedores',
        [ProveedorController::class, 'store']
    )->name('proveedores.store');

    Route::put(
        '/proveedores/{idProveedor}',
        [ProveedorController::class, 'update']
    )->name('proveedores.update');

    Route::delete(
        '/proveedores/{idProveedor}',
        [ProveedorController::class, 'destroy']
    )->name('proveedores.destroy');


    // =====================================================
    // USUARIOS
    // =====================================================

    Route::get(
        '/usuarios',
        [UserController::class, 'index']
    )->name('usuarios.index');

    Route::post(
        '/usuarios',
        [UserController::class, 'store']
    )->name('usuarios.store');


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