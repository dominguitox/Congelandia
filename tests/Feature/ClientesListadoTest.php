<?php

use App\Models\Cliente;
use App\Models\PagoSalida;
use App\Models\Salida;
use App\Models\TipoSalida;
use App\Models\User;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

beforeEach(function () {
    Artisan::call('migrate:fresh');
    $this->withoutVite();
    $this->actingAs(User::create([
        'nombre' => 'Cajero de prueba',
        'email' => 'clientes@test.cl',
        'contrasena' => bcrypt('secreto'),
        'rol' => 'Cajero',
        'activo' => true,
    ]));
});

test('crear un cliente sin compras lo muestra inmediatamente en el listado', function () {
    $this->post(route('clientes.store'), [
        'rutCliente' => '11111111-1',
        'nombre' => 'Cliente recién creado',
        'telefono' => '+56 9 1234 5678',
    ])->assertRedirect(route('clientes.index'))
        ->assertSessionHas('success', 'Cliente creado correctamente');

    $this->assertDatabaseHas('cliente', ['rutCliente' => '11111111-1', 'nombre' => 'Cliente recién creado']);
    $this->get(route('clientes.index'))->assertOk()
        ->assertSee('Cliente recién creado')
        ->assertSee('Cliente creado correctamente')
        ->assertViewHas('clientesSinDeuda', fn ($clientes) => $clientes->pluck('rutCliente')->all() === ['11111111-1'])
        ->assertViewHas('clientesDeudores', fn ($clientes) => $clientes->isEmpty());
});

test('el listado incluye todos los activos y separa deudas de clientes sin deuda', function () {
    $nuevo = Cliente::create(['rutCliente' => '11111111-1', 'nombre' => 'Sin compras']);
    $deudor = Cliente::create(['rutCliente' => '22222222-2', 'nombre' => 'Con deuda']);
    $pagado = Cliente::create(['rutCliente' => '33333333-3', 'nombre' => 'Compra pagada']);
    $eliminado = Cliente::create(['rutCliente' => '44444444-4', 'nombre' => 'Cliente eliminado']);
    $eliminado->delete();
    $tipo = TipoSalida::create(['nombre' => 'Venta']);
    $metodo = DB::table('metodo_pago')->insertGetId(['nombre' => 'Efectivo']);

    foreach ([[$deudor, 200], [$pagado, 1000]] as [$cliente, $monto]) {
        $venta = Salida::create([
            'fecha' => now(),
            'idTipo' => $tipo->idTipo,
            'idUsuario' => auth()->id(),
            'rutCliente' => $cliente->rutCliente,
            'totalSalida' => 1000,
        ]);
        PagoSalida::create([
            'idSalida' => $venta->idSalida,
            'idMetodo' => $metodo,
            'montoPagado' => $monto,
            'fechaPago' => now()->toDateString(),
        ]);
    }

    $this->get(route('clientes.index'))->assertOk()
        ->assertSee('Sin compras')->assertSee('Compra pagada')->assertSee('Con deuda')
        ->assertDontSee('Cliente eliminado')
        ->assertViewHas('clientesSinDeuda', fn ($clientes) => $clientes->pluck('rutCliente')->all() === [$nuevo->rutCliente, $pagado->rutCliente])
        ->assertViewHas('clientesDeudores', fn ($clientes) => $clientes->pluck('rutCliente')->all() === [$deudor->rutCliente])
        ->assertViewHas('deudaTotal', fn ($total) => (float) $total === 800.0);
});

test('un cliente sin compras puede editarse y eliminarse desde el listado', function () {
    $cliente = Cliente::create(['rutCliente' => '11111111-1', 'nombre' => 'Cliente original']);
    $this->put(route('clientes.update', $cliente->rutCliente), [
        'nombre' => 'Cliente actualizado', 'telefono' => '123456789',
    ])->assertRedirect(route('clientes.index'));
    $this->get(route('clientes.index'))->assertOk()->assertSee('Cliente actualizado');

    $this->delete(route('clientes.destroy', $cliente->rutCliente))->assertRedirect(route('clientes.index'));
    $this->get(route('clientes.index'))->assertOk()->assertDontSee('Cliente actualizado');
    expect($cliente->fresh()->trashed())->toBeTrue();
});

test('un RUT duplicado muestra errores y recupera el formulario sin crear otro cliente', function () {
    Cliente::create(['rutCliente' => '11111111-1', 'nombre' => 'Cliente existente']);
    $this->from(route('clientes.index'))->post(route('clientes.store'), [
        'rutCliente' => '11111111-1', 'nombre' => 'Intento duplicado', 'telefono' => '123456789',
    ])->assertRedirect(route('clientes.index'))
        ->assertSessionHasErrors(['rutCliente'], null, 'crearCliente');

    $this->withCookie(config('session.cookie'), session()->getId())->get(route('clientes.index'))->assertOk()
        ->assertSee('No se pudo crear el cliente')
        ->assertSee('Ya existe un cliente con este RUT.')
        ->assertSee('value="Intento duplicado"', false)
        ->assertSee('display: block;', false);
    expect(Cliente::count())->toBe(1);
});

test('los datos invalidos no se guardan y muestran el error en el formulario de alta', function (array $datos, string $campo) {
    $this->from(route('clientes.index'))->post(route('clientes.store'), $datos)
        ->assertRedirect(route('clientes.index'))->assertSessionHasErrors([$campo], null, 'crearCliente');
    // Conservar la cookie como el navegador al seguir el redirect (sesiones JSON).
    $this->withCookie(config('session.cookie'), session()->getId())->get(route('clientes.index'))
        ->assertOk()->assertSee('No se pudo crear el cliente');
    expect(Cliente::count())->toBe(0);
})->with([
    [['rutCliente' => '11111111-1'], 'nombre'],
    [['rutCliente' => '11111111-1', 'nombre' => 'cliente', 'telefono' => str_repeat('1', 21)], 'telefono'],
]);
