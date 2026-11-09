<?php

use App\Models\Producto;
use App\Models\Salida;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

beforeEach(function () {
    Artisan::call('migrate:fresh');
    $this->withoutVite();
    $this->seed(DatabaseSeeder::class);
    $this->actingAs(User::firstOrFail());
    Producto::create(['codigo' => 'COBRO-1', 'nombre' => 'Helado']);
    DB::table('lista_precio')->insert(['codigoProducto' => 'COBRO-1', 'precioVenta' => 6500, 'fechaInicio' => now()->subDay()]);
    $this->efectivo = DB::table('metodo_pago')->where('nombre', 'Efectivo')->value('idMetodo');
    $this->debito = DB::table('metodo_pago')->where('nombre', 'Débito')->value('idMetodo');
    $this->venta = [
        'idTipo' => DB::table('tipo_salida')->where('nombre', 'Venta')->value('idTipo'),
        'claveOperacion' => (string) Str::uuid(),
        'productos' => [['id' => 'COBRO-1', 'cantidad' => 1, 'precio' => 6500]],
        'pagos' => [['idMetodo' => $this->efectivo, 'monto' => 6500]],
        'efectivoRecibido' => 10000,
    ];
});

test('efectivo registra solo el monto aplicado y conserva recibido y vuelto', function () {
    $this->postJson(route('venta.registrar'), $this->venta)->assertOk()
        ->assertJsonPath('total', 6500)->assertJsonPath('efectivoRecibido', 10000)->assertJsonPath('vuelto', 3500);
    $venta = Salida::with('pagos')->firstOrFail();
    expect((float) $venta->pagos->sum('montoPagado'))->toBe(6500.0)
        ->and((float) $venta->efectivoRecibido)->toBe(10000.0)->and((float) $venta->vuelto)->toBe(3500.0);
});

test('pagos mixtos calculan el vuelto solo sobre la parte en efectivo', function () {
    $this->venta['pagos'] = [['idMetodo' => $this->efectivo, 'monto' => 2500], ['idMetodo' => $this->debito, 'monto' => 4000]];
    $this->venta['efectivoRecibido'] = 5000;
    $this->postJson(route('venta.registrar'), $this->venta)->assertOk()->assertJsonPath('vuelto', 2500);
    $this->assertDatabaseHas('pago_salida', ['idMetodo' => $this->efectivo, 'montoPagado' => 2500]);
    $this->assertDatabaseHas('pago_salida', ['idMetodo' => $this->debito, 'montoPagado' => 4000]);
    $this->assertDatabaseCount('salida', 1);
});

test('pago sin efectivo no requiere recibido ni genera vuelto', function () {
    $this->venta['pagos'] = [['idMetodo' => $this->debito, 'monto' => 6500]];
    $this->venta['efectivoRecibido'] = null;
    $this->postJson(route('venta.registrar'), $this->venta)->assertOk()
        ->assertJsonPath('efectivoRecibido', null)->assertJsonPath('vuelto', 0);
});

test('rechaza importes incompletos excesivos o efectivo insuficiente sin guardar movimientos', function (string $caso) {
    if ($caso === 'incompleto') {
        $this->venta['pagos'][0]['monto'] = 6000;
    }
    if ($caso === 'exceso') {
        $this->venta['pagos'][0]['monto'] = 7000;
    }
    if ($caso === 'efectivo insuficiente') {
        $this->venta['efectivoRecibido'] = 5000;
    }
    if ($caso === 'sin recibido') {
        unset($this->venta['efectivoRecibido']);
    }
    if ($caso === 'negativo') {
        $this->venta['pagos'][0]['monto'] = -1;
    }
    if ($caso === 'fraccionario') {
        $this->venta['pagos'][0]['monto'] = 6500.5;
    }
    if ($caso === 'duplicado') {
        $this->venta['pagos'] = [['idMetodo' => $this->efectivo, 'monto' => 2500], ['idMetodo' => $this->efectivo, 'monto' => 4000]];
    }
    if ($caso === 'metodo eliminado') {
        DB::table('metodo_pago')->where('idMetodo', $this->efectivo)->update(['deleted_at' => now()]);
    }
    if ($caso === 'efectivo con tarjeta') {
        $this->venta['pagos'][0]['idMetodo'] = $this->debito;
    }
    $this->postJson(route('venta.registrar'), $this->venta)->assertUnprocessable();
    $this->assertDatabaseCount('salida', 0);
    $this->assertDatabaseCount('detalle_salida', 0);
    $this->assertDatabaseCount('pago_salida', 0);
})->with(['incompleto', 'exceso', 'efectivo insuficiente', 'sin recibido', 'negativo', 'fraccionario', 'duplicado', 'metodo eliminado', 'efectivo con tarjeta']);

test('reintentar la misma operacion devuelve la venta original incluso si el precio cambia', function () {
    $primera = $this->postJson(route('venta.registrar'), $this->venta)->assertOk();
    DB::table('lista_precio')->update(['precioVenta' => 9999]);
    $this->postJson(route('venta.registrar'), $this->venta)->assertOk()
        ->assertJsonPath('idSalida', $primera->json('idSalida'))->assertJsonPath('repetida', true)->assertJsonPath('vuelto', 3500);
    $this->assertDatabaseCount('salida', 1);
    $this->assertDatabaseCount('detalle_salida', 1);
    $this->assertDatabaseCount('pago_salida', 1);
});

test('la clave de una operacion no se puede reutilizar con otro cobro ni otro usuario', function () {
    $this->postJson(route('venta.registrar'), $this->venta)->assertOk();
    $alterada = $this->venta;
    $alterada['efectivoRecibido'] = 20000;
    $this->postJson(route('venta.registrar'), $alterada)->assertConflict();
    $otro = User::create(['nombre' => 'Otro cajero', 'email' => 'otro@cobro.test', 'contrasena' => 'secret', 'rol' => 'Cajero', 'activo' => true]);
    $this->actingAs($otro)->postJson(route('venta.registrar'), $this->venta)->assertConflict();
    $this->assertDatabaseCount('salida', 1);
});

test('no acepta pagos mixtos sin clave de operacion ni un cliente inexistente', function () {
    $sinClave = $this->venta;
    unset($sinClave['claveOperacion']);
    $this->postJson(route('venta.registrar'), $sinClave)->assertUnprocessable()->assertJsonValidationErrors('claveOperacion');
    $this->venta['rutCliente'] = 'no-existe';
    $this->postJson(route('venta.registrar'), $this->venta)->assertUnprocessable()->assertJsonValidationErrors('rutCliente');
    $this->assertDatabaseCount('salida', 0);
});
