<?php

use App\Models\Promocion;
use App\Models\Producto;
use App\Models\TipoSalida;
use App\Models\User;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

function prepararPosConOferta(float $descuento = 20): User
{
    Artisan::call('migrate:fresh');

    $user = User::create([
        'nombre' => 'Admin POS ofertas',
        'email' => 'pos-ofertas@congelandia.test',
        'contrasena' => 'secret',
        'rol' => 'Administrador',
        'activo' => true,
    ]);
    Producto::create(['codigo' => 'POS-OFERTA-1', 'nombre' => 'Helado en oferta']);
    DB::table('IMAGEN_PRODUCTO')->insert([
        'rutaImagen' => 'images/productos/helado-oferta.jpg',
        'codigoProducto' => 'POS-OFERTA-1',
        'alt' => 'Helado en oferta',
        'descripcion' => 'Imagen del producto',
    ]);

    DB::table('Lista_Precio')->insert([
        'codigoProducto' => 'POS-OFERTA-1',
        'precioVenta' => 1000,
        'fechaInicio' => now()->subDay(),
        'fechaFin' => null,
    ]);
    Promocion::create([
        'codigoProducto' => 'POS-OFERTA-1',
        'porcentajeDescuento' => $descuento,
        'fechaInicio' => now()->subMinute(),
        'fechaFin' => now()->addDay(),
        'activa' => true,
    ]);

    return $user;
}

test('el POS muestra precio normal y precio con oferta en el producto', function () {
    $user = prepararPosConOferta();
    DB::table('Metodo_Pago')->insert(['nombre' => 'Efectivo']);

    $this->actingAs($user)
        ->get(route('pos.index'))
        ->assertOk()
        ->assertSee('Normal: $1.000')
        ->assertSee('$800')
        ->assertSee('data-precio-normal="1000"', false)
        ->assertSee('data-precio-oferta="800"', false)
        ->assertSee('class="badge badge-oferta">-20%</span>', false)
        ->assertSee('src="' . asset('images/productos/helado-oferta.jpg') . '"', false)
        ->assertSee('id="badge-carrito-POS-OFERTA-1" hidden', false)
        ->assertDontSee('Oferta: $800');
});

test('la venta registra el precio de oferta calculado por el servidor', function () {
    $user = prepararPosConOferta();
    $tipo = TipoSalida::create(['nombre' => 'Venta']);
    $metodo = DB::table('Metodo_Pago')->insertGetId(['nombre' => 'Efectivo']);

    $response = $this->actingAs($user)->postJson(route('venta.registrar'), [
        'idTipo' => $tipo->idTipo,
        'rutCliente' => null,
        'totalVenta' => 999999,
        'metodoPago' => $metodo,
        'productos' => [[
            'id' => 'POS-OFERTA-1',
            'nombre' => 'Helado en oferta',
            'cantidad' => 2,
            'precio' => 800,
        ]],
    ]);

    $response->assertOk()->assertJsonPath('success', true);
    $salida = \App\Models\Salida::with('detalles', 'pagos')->firstOrFail();
    expect((float) $salida->totalSalida)->toBe(1600.0)
        ->and((float) $salida->detalles->first()->precioCobrado)->toBe(800.0)
        ->and((float) $salida->pagos->first()->montoPagado)->toBe(1600.0);
});

test('rechaza el cobro si el precio guardado en el carrito quedó desactualizado', function () {
    $user = prepararPosConOferta();
    $tipo = TipoSalida::create(['nombre' => 'Venta']);
    $metodo = DB::table('Metodo_Pago')->insertGetId(['nombre' => 'Efectivo']);

    $this->actingAs($user)->postJson(route('venta.registrar'), [
        'idTipo' => $tipo->idTipo,
        'rutCliente' => null,
        'totalVenta' => 1000,
        'metodoPago' => $metodo,
        'productos' => [['id' => 'POS-OFERTA-1', 'cantidad' => 1, 'precio' => 1000]],
    ])->assertStatus(409)->assertJsonPath('success', false);

    expect(\App\Models\Salida::count())->toBe(0);
});
