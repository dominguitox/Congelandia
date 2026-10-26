<?php

use App\Models\Cliente;
use App\Models\DetalleSalida;
use App\Models\PagoSalida;
use App\Models\Producto;
use App\Models\Salida;
use App\Models\TipoSalida;
use App\Models\User;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

test('migrate crea todas las tablas del negocio', function () {
    Artisan::call('migrate:fresh');

    $tablas = [
        'Usuario', 'Cliente', 'Categoria', 'Producto', 'Lista_Precio', 'Promocion',
        'Proveedor', 'Ingreso', 'Detalle_Ingreso', 'Tipo_Salida', 'Salida',
        'Detalle_Salida', 'Metodo_Pago', 'Pago_Salida', 'Tipo_Devolucion',
        'Devolucion', 'IMAGEN_PRODUCTO', 'Versiones', 'log_errors',
    ];

    foreach ($tablas as $t) {
        expect(Schema::hasTable($t))->toBeTrue("Falta la tabla {$t}");
    }
});

test('el grafo de FK soporta un flujo de venta completo', function () {
    Artisan::call('migrate:fresh');

    $usuario = User::create([
        'nombre' => 'Test',
        'email' => 'test@congelandia.cl',
        'contrasena' => 'secret',
        'rol' => 'Administrador',
        'activo' => true,
    ]);
    $cliente = Cliente::create(['rutCliente' => '11111111-1', 'nombre' => 'Test Cliente']);
    $producto = Producto::create([
        'codigo' => 'TEST-001',
        'nombre' => 'Producto test',
        'idCategoria' => DB::table('Categoria')->insertGetId(['nombre' => 'TestCat']),
    ]);
    $tipo = TipoSalida::create(['nombre' => 'venta test']);
    $metodo = DB::table('Metodo_Pago')->insertGetId(['nombre' => 'efectivo']);

    $salida = Salida::create([
        'fecha' => now(),
        'idTipo' => $tipo->idTipo,
        'idUsuario' => $usuario->idUsuario,
        'rutCliente' => $cliente->rutCliente,
        'totalSalida' => 1000,
    ]);
    $detalle = DetalleSalida::create([
        'idSalida' => $salida->idSalida,
        'codigoProducto' => $producto->codigo,
        'cantidad' => 2,
        'precioCobrado' => 500,
    ]);
    PagoSalida::create([
        'idSalida' => $salida->idSalida,
        'idMetodo' => $metodo,
        'montoPagado' => 1000,
        'fechaPago' => now()->toDateString(),
    ]);

    expect($salida->usuario->idUsuario)->toBe($usuario->idUsuario)
        ->and($salida->detalles()->count())->toBe(1)
        ->and($salida->pagos()->count())->toBe(1)
        ->and($detalle->producto->codigo)->toBe($producto->codigo);
});

test('una FK invalida es rechazada', function () {
    Artisan::call('migrate:fresh');

    $usuario = User::create([
        'nombre' => 'Test',
        'email' => 'test@congelandia.cl',
        'contrasena' => 'secret',
        'rol' => 'Administrador',
        'activo' => true,
    ]);

    $lanza = false;
    try {
        Salida::create([
            'fecha' => now(),
            'idTipo' => 9999,
            'idUsuario' => $usuario->idUsuario,
            'totalSalida' => 0,
        ]);
    } catch (Throwable $e) {
        $lanza = true;
    }

    expect($lanza)->toBeTrue();
});
