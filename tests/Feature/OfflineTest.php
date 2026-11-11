<?php

use App\Models\Ingreso;
use App\Models\Producto;
use App\Models\Salida;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

beforeEach(function () {
    Artisan::call('migrate:fresh'); // Solo la base de pruebas aislada, igual que ProductosStockTest.
    $this->withoutVite();
    $this->seed(DatabaseSeeder::class);
    $this->user = User::firstOrFail();
    $this->actingAs($this->user);
    Producto::create(['codigo' => 'OFF-1', 'nombre' => 'Helado offline']);
    DB::table('lista_precio')->insert(['codigoProducto' => 'OFF-1', 'precioVenta' => 500, 'fechaInicio' => now()->subDay()]);
    $ingreso = Ingreso::create(['fecha' => now(), 'idUsuario' => $this->user->idUsuario, 'totalCompra' => 1000]);
    $ingreso->detalles()->create(['codigoProducto' => 'OFF-1', 'cantidad' => 10, 'precioCompra' => 100, 'fechaVencimiento' => now()->addMonth()->toDateString()]);
    $this->efectivo = DB::table('metodo_pago')->where('nombre', 'Efectivo')->value('idMetodo');
    $this->tipoVenta = DB::table('tipo_salida')->where('nombre', 'Venta')->value('idTipo');
});

function operacionOffline($test, string $tipo = 'venta', int $stock = 10): array
{
    return [
        'id' => (string) Str::uuid(), 'usuario' => $test->user->idUsuario, 'tipo' => $tipo,
        'fechaLocal' => now()->toIso8601String(),
        'datos' => match ($tipo) {
            'venta' => ['idTipo' => $test->tipoVenta, 'productos' => [['id' => 'OFF-1', 'cantidad' => 2, 'precio' => 500, 'stock' => $stock]],
                'pagos' => [['idMetodo' => $test->efectivo, 'monto' => 1000]], 'efectivoRecibido' => 2000],
            'ingreso' => ['codigoProducto' => 'OFF-1', 'cantidad' => 5, 'precioCompra' => 100, 'idProveedor' => null,
                'fechaVencimiento' => now()->addMonth()->toDateString(), 'stock' => $stock],
            'merma' => ['codigoProducto' => 'OFF-1', 'cantidad' => 3, 'motivo' => 'Merma por Daño', 'stock' => $stock],
        },
    ];
}

test('la copia usa datos mínimos y no expone contraseñas ni métodos fiados', function () {
    DB::table('metodo_pago')->insert(['nombre' => 'Fiado']);
    $response = $this->getJson(route('offline.datos'))->assertOk()->assertHeader('Cache-Control', 'no-store, private')
        ->assertJsonPath('usuario.id', $this->user->idUsuario)->assertJsonPath('productos.0.stock', 10)->assertJsonPath('productos.0.precio', 500);
    expect($response->getContent())->not->toContain('contrasena', 'password', 'Fiado', 'csrf');
    $this->get(route('offline.index'))->assertOk()->assertDontSee($this->user->email)->assertDontSee('csrf-token');
});

test('sincroniza ingreso venta y merma en orden sin alterar las reglas de stock', function () {
    $this->postJson(route('offline.sync'), operacionOffline($this, 'ingreso'))->assertOk();
    $this->postJson(route('offline.sync'), operacionOffline($this, 'venta', 15))->assertOk()->assertJsonPath('vuelto', 1000);
    $this->postJson(route('offline.sync'), operacionOffline($this, 'merma', 13))->assertOk();
    expect((int) Producto::conDetalle()->firstOrFail()->stock)->toBe(10);
    $this->assertDatabaseCount('operaciones_offline', 3);
    $this->assertDatabaseCount('registro_acciones', 3);
    $this->assertDatabaseHas('detalle_ingreso', ['cantidad' => 5, 'precioCompra' => 100]);
});

test('repetir una operación confirmada no duplica movimientos aunque cambien los catálogos', function (string $tipo) {
    $op = operacionOffline($this, $tipo);
    $this->postJson(route('offline.sync'), $op)->assertOk();
    DB::table('lista_precio')->update(['precioVenta' => 900]);
    DB::table('metodo_pago')->update(['deleted_at' => now()]);
    $this->postJson(route('offline.sync'), $op)->assertOk()->assertJsonPath('repetida', true);
    $this->assertDatabaseCount('operaciones_offline', 1);
    $this->assertDatabaseCount('registro_acciones', 1);
    expect(Ingreso::count())->toBe($tipo === 'ingreso' ? 2 : 1);
    expect(Salida::count())->toBe($tipo === 'ingreso' ? 0 : 1);
})->with(['venta', 'ingreso', 'merma']);

test('una UUID no acepta datos distintos ni otro usuario', function () {
    $op = operacionOffline($this);
    $this->postJson(route('offline.sync'), $op)->assertOk();
    $modified = $op;
    $modified['datos']['efectivoRecibido'] = 5000;
    $this->postJson(route('offline.sync'), $modified)->assertConflict();
    $other = User::create(['nombre' => 'Otro', 'email' => 'otro@offline.test', 'contrasena' => 'test', 'rol' => 'Cajero', 'activo' => true]);
    $this->actingAs($other)->postJson(route('offline.sync'), $op)->assertForbidden();
    $op['usuario'] = $other->idUsuario;
    $this->postJson(route('offline.sync'), $op)->assertConflict();
    $this->assertDatabaseCount('salida', 1);
});

test('discrepancias de stock o precio guardan el original sin aplicar ni resolver movimientos', function (string $campo) {
    $op = operacionOffline($this);
    if ($campo === 'stock') {
        $op['datos']['productos'][0]['stock'] = 12;
    } else {
        DB::table('lista_precio')->update(['precioVenta' => 600]);
    }
    $this->postJson(route('offline.sync'), $op)->assertConflict()->assertJsonPath('estado', 'conflicto')
        ->assertJsonPath('conflictos.0.campo', $campo);
    $this->assertDatabaseCount('salida', 0);
    $stored = DB::table('operaciones_offline')->first();
    expect(json_decode($stored->datos, true))->toBe($op);
    $this->postJson(route('offline.sync'), $op)->assertConflict()->assertJsonPath('repetida', true);
    expect((int) Producto::conDetalle()->firstOrFail()->stock)->toBe(10);
})->with(['stock', 'precio']);

test('merma excesiva y motivos nuevos no crean salidas', function () {
    $op = operacionOffline($this, 'merma');
    $op['datos']['cantidad'] = 11;
    $this->postJson(route('offline.sync'), $op)->assertConflict();
    $op = operacionOffline($this, 'merma');
    $op['datos']['motivo'] = 'Ajuste libre';
    $this->postJson(route('offline.sync'), $op)->assertUnprocessable();
    $this->assertDatabaseCount('salida', 0);
});

test('pagos inválidos o fiados revierten la venta y permiten reintentar los mismos datos', function (string $caso) {
    $op = operacionOffline($this);
    if ($caso === 'fiado') {
        $op['datos']['pagos'][0]['idMetodo'] = DB::table('metodo_pago')->insertGetId(['nombre' => 'Fiado']);
    } else {
        $op['datos']['pagos'][0]['monto'] = 999;
    }
    $this->postJson(route('offline.sync'), $op)->assertUnprocessable();
    $this->assertDatabaseCount('salida', 0);
    $this->assertDatabaseCount('pago_salida', 0);
    $this->assertDatabaseCount('operaciones_offline', 0);
})->with(['importe', 'fiado']);

test('pagos mixtos offline conservan importes recibido y vuelto', function () {
    $op = operacionOffline($this);
    $debito = DB::table('metodo_pago')->where('nombre', 'Débito')->value('idMetodo');
    $op['datos']['pagos'] = [['idMetodo' => $this->efectivo, 'monto' => 400], ['idMetodo' => $debito, 'monto' => 600]];
    $op['datos']['efectivoRecibido'] = 1000;
    $this->postJson(route('offline.sync'), $op)->assertOk()->assertJsonPath('vuelto', 600);
    $this->assertDatabaseHas('pago_salida', ['idMetodo' => $this->efectivo, 'montoPagado' => 400]);
    $this->assertDatabaseHas('pago_salida', ['idMetodo' => $debito, 'montoPagado' => 600]);
});

test('sesión vencida o usuario desactivado no aceptan sincronizaciones', function () {
    $op = operacionOffline($this);
    $this->user->update(['activo' => false]);
    $this->postJson(route('offline.sync'), $op)->assertUnauthorized();
    $this->getJson(route('offline.datos'))->assertUnauthorized();
    $this->getJson(route('offline.sesion'))->assertUnauthorized();
    $this->assertDatabaseCount('operaciones_offline', 0);
});

test('un ingreso se valida contra su fecha original aunque se sincronice después del vencimiento', function () {
    $op = operacionOffline($this, 'ingreso');
    $op['fechaLocal'] = now()->subDays(3)->toIso8601String();
    $op['datos']['fechaVencimiento'] = now()->subDay()->toDateString();
    $this->postJson(route('offline.sync'), $op)->assertOk();
    $this->assertDatabaseHas('detalle_ingreso', ['fechaVencimiento' => $op['datos']['fechaVencimiento'], 'cantidad' => 5]);
});
