<?php

use App\Models\Producto;
use App\Models\Salida;
use App\Models\TipoSalida;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Database\Seeders\TipoSalidaSeeder;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

beforeEach(function () {
    Artisan::call('migrate:fresh');
    $this->withoutVite();
});

test('el seeder general se puede repetir sin duplicar usuarios ni catalogos ni cambiar sus ids', function () {
    $this->seed(DatabaseSeeder::class);
    $usuarios = DB::table('usuario')->pluck('idUsuario', 'email')->all();
    $metodos = DB::table('metodo_pago')->pluck('idMetodo', 'nombre')->all();
    $tipos = DB::table('tipo_salida')->pluck('idTipo', 'nombre')->all();

    $this->seed(DatabaseSeeder::class);

    expect(DB::table('usuario')->pluck('idUsuario', 'email')->all())->toBe($usuarios)
        ->and(DB::table('metodo_pago')->pluck('idMetodo', 'nombre')->all())->toBe($metodos)
        ->and(DB::table('tipo_salida')->pluck('idTipo', 'nombre')->all())->toBe($tipos);
    $this->assertDatabaseCount('usuario', 1);
    $this->assertDatabaseCount('metodo_pago', 3);
    $this->assertDatabaseCount('tipo_salida', 3);
    foreach (['Venta', 'Merma por Vencimiento', 'Merma por Daño'] as $nombre) {
        $this->assertDatabaseHas('tipo_salida', ['nombre' => $nombre, 'deleted_at' => null]);
    }
});

test('los tipos de salida recuperan su registro eliminado sin renumerar ni alterar tipos propios', function () {
    $propio = TipoSalida::create(['nombre' => 'Otro movimiento']);
    $venta = TipoSalida::create(['nombre' => 'Venta']);
    $venta->delete();

    $this->seed(TipoSalidaSeeder::class);
    $this->seed(TipoSalidaSeeder::class);

    $this->assertDatabaseCount('tipo_salida', 4);
    $this->assertDatabaseHas('tipo_salida', ['idTipo' => $venta->idTipo, 'nombre' => 'Venta', 'deleted_at' => null]);
    $this->assertDatabaseHas('tipo_salida', ['idTipo' => $propio->idTipo, 'nombre' => 'Otro movimiento', 'deleted_at' => null]);
});

test('repetir los seeds conserva la cuenta administradora existente aunque este eliminada', function () {
    $this->seed(DatabaseSeeder::class);
    $usuario = User::where('email', 'admin@congelandia.cl')->firstOrFail();
    $usuario->update(['nombre' => 'Cuenta personalizada', 'contrasena' => 'hash-personalizado', 'rol' => 'Cajero', 'activo' => false]);
    $usuario->delete();
    $eliminado = $usuario->deleted_at->toDateTimeString();

    $this->seed(DatabaseSeeder::class);

    $this->assertDatabaseCount('usuario', 1);
    $this->assertDatabaseHas('usuario', [
        'idUsuario' => $usuario->idUsuario,
        'nombre' => 'Cuenta personalizada',
        'contrasena' => 'hash-personalizado',
        'rol' => 'Cajero',
        'activo' => false,
        'deleted_at' => $eliminado,
    ]);
});

test('el POS usa el tipo Venta real aunque no tenga id uno y rechaza otros tipos al cobrar', function () {
    $otroTipo = TipoSalida::create(['nombre' => 'Otro movimiento']);
    $this->seed(DatabaseSeeder::class);
    $usuario = User::where('email', 'admin@congelandia.cl')->firstOrFail();
    $tipoVenta = TipoSalida::where('nombre', 'Venta')->firstOrFail();
    $metodo = DB::table('metodo_pago')->where('nombre', 'Efectivo')->value('idMetodo');
    Producto::create(['codigo' => 'SEED-1', 'nombre' => 'Producto de prueba']);
    DB::table('lista_precio')->insert([
        'codigoProducto' => 'SEED-1', 'precioVenta' => 1000,
        'fechaInicio' => now()->subDay(), 'fechaFin' => null,
    ]);
    $venta = [
        'idTipo' => $tipoVenta->idTipo,
        'metodoPago' => $metodo,
        'productos' => [['id' => 'SEED-1', 'cantidad' => 2, 'precio' => 1000]],
    ];

    $this->actingAs($usuario)->get(route('pos.index'))->assertOk()
        ->assertSee('data-tipo-venta="'.$tipoVenta->idTipo.'"', false);
    $this->postJson(route('venta.registrar'), array_replace($venta, ['idTipo' => $otroTipo->idTipo]))
        ->assertUnprocessable()->assertJsonValidationErrors('idTipo');
    $this->assertDatabaseCount('salida', 0);

    $this->postJson(route('venta.registrar'), $venta)->assertOk()->assertJsonPath('success', true);
    expect(Salida::firstOrFail()->idTipo)->toBe($tipoVenta->idTipo);

    $tipoVenta->delete();
    $this->postJson(route('venta.registrar'), $venta)->assertUnprocessable()->assertJsonValidationErrors('idTipo');
    $this->assertDatabaseCount('salida', 1);
});
