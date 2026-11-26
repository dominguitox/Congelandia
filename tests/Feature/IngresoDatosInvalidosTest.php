<?php

use App\Models\DetalleIngreso;
use App\Models\Ingreso;
use App\Models\Producto;
use App\Models\User;
use Illuminate\Support\Facades\Artisan;

beforeEach(function () {
    Artisan::call('migrate:fresh');
    $this->withoutVite();
    $this->usuario = User::create([
        'nombre' => 'Validador de ingresos',
        'email' => 'validacion-ingresos@congelandia.test',
        'contrasena' => bcrypt('secreto'),
        'rol' => 'Administrador',
        'activo' => true,
    ]);
    Producto::create(['codigo' => 'VAL-1', 'nombre' => 'Helado de validación']);
});

test('rechaza datos invalidos en el ingreso sin crear registros', function (array $datos, array $campos) {
    $this->actingAs($this->usuario)
        ->postJson(route('inventario.ingresos.store'), $datos)
        ->assertUnprocessable()
        ->assertJsonStructure(['message', 'errors'])
        ->assertJsonValidationErrors($campos);

    expect(Ingreso::withTrashed()->count())->toBe(0)
        ->and(DetalleIngreso::withTrashed()->count())->toBe(0);
})->with([
    'sin datos' => [
        [],
        ['codigoProducto', 'cantidad', 'precioCompra', '_modal'],
    ],
    'producto inexistente' => [
        ['codigoProducto' => 'NO-EXISTE', 'cantidad' => 5, 'precioCompra' => 1000,
            'fechaVencimiento' => now()->addMonth()->toDateString(), '_modal' => 'modalRegistrarIngreso'],
        ['codigoProducto'],
    ],
    'cantidad cero' => [
        ['codigoProducto' => 'VAL-1', 'cantidad' => 0, 'precioCompra' => 1000,
            'fechaVencimiento' => now()->addMonth()->toDateString(), '_modal' => 'modalRegistrarIngreso'],
        ['cantidad'],
    ],
    'precio negativo' => [
        ['codigoProducto' => 'VAL-1', 'cantidad' => 5, 'precioCompra' => -100,
            'fechaVencimiento' => now()->addMonth()->toDateString(), '_modal' => 'modalRegistrarIngreso'],
        ['precioCompra'],
    ],
    'modal incorrecto' => [
        ['codigoProducto' => 'VAL-1', 'cantidad' => 5, 'precioCompra' => 1000,
            'fechaVencimiento' => now()->addMonth()->toDateString(), '_modal' => 'modalRegistrarSalida'],
        ['_modal'],
    ],
]);
