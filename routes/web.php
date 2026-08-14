<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('dashboard.index');
});

Route::get('/pos', function () {
    return view('pos.index');
});

Route::get('/inventario', function () {
    return view('inventario.index');
});

Route::get('/reportes', function () {
    return view('reportes.index');
});