<?php

use App\Models\Producto;
use App\Models\User;
use Congelandia\Migration\PdvHistory;
use Congelandia\Migration\PdvMigration;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

require_once __DIR__.'/../../tools/migration/PdvMigration.php';
require_once __DIR__.'/../../tools/migration/PdvHistory.php';

function executePdvSql(string $sql): array
{
    return PdvMigration::execute(DB::connection()->getPdo(), $sql);
}

beforeEach(function () {
    if (DB::connection()->getDriverName() !== 'mysql') {
        $this->markTestSkipped('Verificación de SQL MySQL: ejecutada en la matriz mysql de CI.');
    }
    Artisan::call('migrate:fresh');
    $this->seed(DatabaseSeeder::class);
    $this->withoutVite();
    $this->actingAs(User::firstOrFail());
    $this->pdvPlan = [
        'manifest' => ['source_sha256' => hash('sha256', 'mysql-fixture')], 'cutoff' => now()->subDay()->format('Y-m-d H:i:s'), 'stockErrors' => [],
        'products' => [
            ['code' => '00123', 'name' => "Piñón d'oro", 'category' => 'Congelados', 'price' => '1500.00', 'cost' => '1000.00', 'stock' => 6, 'active' => true],
            ['code' => 'INACTIVO', 'name' => 'Venta por revisar', 'category' => 'Congelados', 'price' => '2000.00', 'cost' => '800.00', 'stock' => 0, 'active' => false],
        ],
    ];
});

test('importa con claves foraneas activas conserva costos y no crea ventas ni deuda', function () {
    expect(executePdvSql(PdvMigration::catalogSql($this->pdvPlan))[0]['resultado'])->toBe('IMPORTADO');
    expect(executePdvSql(PdvMigration::stockSql($this->pdvPlan))[0]['resultado'])->toBe('IMPORTADO');
    expect(executePdvSql(PdvMigration::verificationSql($this->pdvPlan)))->toBe([]);
    $product = Producto::conDetalle()->firstOrFail();
    expect($product->codigo)->toBe('00123')->and((int) $product->stock)->toBe(6)->and((float) $product->precio)->toBe(1500.0)->and((float) $product->costo)->toBe(1000.0);
    $this->assertDatabaseCount('salida', 0);
    $this->assertDatabaseCount('cliente', 0);
    $this->assertDatabaseHas('ingreso', ['totalCompra' => 0, 'idProveedor' => null]);
    $this->get('/inventario')->assertOk();
    $this->get('/pos')->assertOk();
    $this->get('/dashboard')->assertOk()->assertViewHas('totalVentasHoy', 0);
    $this->get('/reportes')->assertOk();
    $this->postJson('/venta/registrar', [
        'idTipo' => DB::table('tipo_salida')->where('nombre', 'Venta')->value('idTipo'),
        'metodoPago' => DB::table('metodo_pago')->where('nombre', 'Efectivo')->value('idMetodo'),
        'productos' => [['id' => '00123', 'cantidad' => 1, 'precio' => 1500]],
    ])->assertOk()->assertJsonPath('total', 1500);
    expect((int) Producto::conDetalle()->firstOrFail()->stock)->toBe(5);
});

test('repetir ambas etapas no duplica productos precios ni aperturas', function () {
    foreach (['IMPORTADO', 'YA_IMPORTADO'] as $expected) {
        expect(executePdvSql(PdvMigration::catalogSql($this->pdvPlan))[0]['resultado'])->toBe($expected);
        expect(executePdvSql(PdvMigration::stockSql($this->pdvPlan))[0]['resultado'])->toBe($expected);
    }
    $this->assertDatabaseCount('producto', 2);
    $this->assertDatabaseCount('lista_precio', 2);
    $this->assertDatabaseCount('ingreso', 1);
    $this->assertDatabaseCount('detalle_ingreso', 2);
    expect(executePdvSql(PdvMigration::verificationSql($this->pdvPlan)))->toBe([]);
});

test('no modifica un destino con productos ni permite apertura antes del catalogo', function () {
    expect(executePdvSql(PdvMigration::stockSql($this->pdvPlan))[0]['resultado'])->toStartWith('BLOQUEADO');
    Producto::create(['codigo' => 'EXISTENTE', 'nombre' => 'Conservar']);
    expect(executePdvSql(PdvMigration::catalogSql($this->pdvPlan))[0]['resultado'])->toStartWith('BLOQUEADO');
    $this->assertDatabaseCount('producto', 1);
    $this->assertDatabaseCount('ingreso', 0);
});

test('un error intermedio revierte la transaccion y permite repetir', function () {
    $sql = PdvMigration::catalogSql($this->pdvPlan);
    $sql = str_replace('INSERT INTO lista_precio', 'INSERT INTO tabla_que_no_existe', $sql);
    expect(fn () => executePdvSql($sql))->toThrow(PDOException::class);
    $this->assertDatabaseCount('producto', 0);
    $this->assertDatabaseCount('categoria', 0);
    expect(executePdvSql(PdvMigration::catalogSql($this->pdvPlan))[0]['resultado'])->toBe('IMPORTADO');
});

test('historial nativo usa usuario sin acceso y tipo historico sin inventar pagos ni duplicar stock', function () {
    $plan = $this->pdvPlan;
    $plan['products'] = [$plan['products'][0]];
    $plan['original_count'] = 3;
    $base = ['source_user' => 7, 'date' => $plan['cutoff'], 'code' => '00123', 'before' => 0, 'kind' => 'original'];
    $plan['events'] = [
        $base + ['source_id' => 1, 'source_type' => 'e', 'direction' => 'ingreso', 'quantity' => 10, 'delta' => 10, 'unit' => '1000.00', 'amount' => '10000.00', 'type' => 'Ajuste histórico'],
        $base + ['source_id' => 2, 'source_type' => 's', 'direction' => 'salida', 'quantity' => 4, 'delta' => -4, 'unit' => '1500.00', 'amount' => '6000.00', 'type' => 'Salida histórica'],
        $base + ['source_id' => 3, 'source_type' => 'a', 'direction' => 'ingreso', 'quantity' => 0, 'delta' => 0, 'unit' => '1000.00', 'amount' => '0.00', 'type' => 'Ajuste histórico'],
    ];
    executePdvSql(PdvMigration::catalogSql($plan));
    $sql = PdvHistory::sql($plan);
    expect(executePdvSql($sql)[0]['resultado'])->toBe('IMPORTADO');
    expect(executePdvSql($sql)[0]['resultado'])->toBe('YA_IMPORTADO');
    expect(executePdvSql(PdvMigration::verificationSql($plan)))->toBe([])
        ->and(executePdvSql(PdvHistory::verificationSql($plan)))->toBe([]);
    $historical = User::where('email', 'historico@congelandia.invalid')->firstOrFail();
    expect($historical->nombre)->toBe('Usuario histórico')->and($historical->activo)->toBeFalse();
    $this->assertDatabaseHas('ingreso', ['idUsuario' => $historical->idUsuario]);
    $this->assertDatabaseHas('salida', ['idUsuario' => $historical->idUsuario]);
    $this->assertDatabaseCount('pago_salida', 0);
    $this->get('/historial?buscar=00123')->assertOk()->assertSee('Usuario histórico')->assertSee('Salida histórica');
    $date = substr($plan['cutoff'], 0, 10);
    $this->get('/reportes?fecha_inicio='.$date.'&fecha_fin='.$date)->assertOk()
        ->assertViewHas('datos', fn ($data) => $data['ingresos'] === 6000.0 && $data['historialReferencia']);
});
