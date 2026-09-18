<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProductoController;

Route::get('/', function () {
    return view('dashboard.index');
});

Route::get('/dashboard', function () {
    return view('dashboard.index');
});

Route::get('/pos', function () {
    return view('pos.index');
});

Route::get('/reportes', function () {
    return view('reportes.index');
});

// Rutas de Inventario
Route::get('/inventario', [ProductoController::class, 'listarProductos'])->name('inventario.index');

// Ruta para ver un producto específico usando su código
Route::get('/inventario/{codigo}', [ProductoController::class, 'show'])->name('inventario.show');

// Agrupamos las rutas protegidas por autenticación y rol
Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::post('/productos/guardar', [ProductoController::class, 'crearProducto'])->name('productos.crearProducto');
});

use App\Http\Controllers\Auth\Login;
use App\Http\Controllers\Auth\Logout;



// Login routes
Route::view('/login', 'auth.login')
    ->middleware('guest')
    ->name('login');

Route::post('/login', Login::class)
    ->middleware('guest');

// Logout route
Route::post('/logout', Logout::class)
    ->middleware('auth')
    ->name('logout');
    

    