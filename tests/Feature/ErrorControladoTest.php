<?php

use App\Models\Producto;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

beforeEach(function () {
    Artisan::call('migrate:fresh');
    $this->withoutVite();
    $this->seed(DatabaseSeeder::class);
    $this->usuario = User::firstOrFail();
    $this->actingAs($this->usuario);

    Producto::create(['codigo' => 'ERR-1', 'nombre' => 'Helado de prueba']);
    DB::table('lista_precio')->insert([
        'codigoProducto' => 'ERR-1',
        'precioVenta' => 6500,
        'fechaInicio' => now()->subDay(),
    ]);

    $this->idTipoVenta = DB::table('tipo_salida')->where('nombre', 'Venta')->value('idTipo');
    $this->idEfectivo = DB::table('metodo_pago')->where('nombre', 'Efectivo')->value('idMetodo');
});

test('un error controlado responde sin exponer trazas ni credenciales', function () {
    config(['app.debug' => true]);
    $this->assertTrue(config('app.debug'));

    // La tabla de pagos desaparece: el cobro debe fallar de forma controlada.
    Schema::drop('pago_salida');

    $response = $this->postJson(route('venta.registrar'), [
        'idTipo' => $this->idTipoVenta,
        'metodoPago' => $this->idEfectivo,
        'productos' => [['id' => 'ERR-1', 'cantidad' => 1, 'precio' => 6500]],
    ]);

    $response->assertStatus(500)
        ->assertJsonPath('success', false)
        ->assertJsonPath('error', 'No se pudo registrar la venta. Reintenta el mismo cobro.')
        ->assertJsonStructure(['success', 'error'])
        ->assertDontSee('SQLSTATE')
        ->assertDontSee('Stack trace')
        ->assertDontSee('vendor/')
        ->assertDontSee('CobroVenta')
        ->assertDontSee('APP_KEY')
        ->assertDontSee('DB_PASSWORD')
        ->assertDontSee('contrasena')
        ->assertDontSee('password');

    $contenido = $response->getContent();
    expect($contenido)->not->toContain((string) $this->usuario->contrasena);

    $claveApp = (string) config('app.key');
    if ($claveApp !== '') {
        expect($contenido)->not->toContain($claveApp);
    }

    $this->assertDatabaseCount('salida', 0);
    $this->assertDatabaseCount('detalle_salida', 0);
});
