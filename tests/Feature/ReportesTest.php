<?php

use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);

beforeEach(function () {
    config(['app.key' => 'base64:'.base64_encode(str_repeat('r', 32))]);
    $this->withoutVite();
    $this->travelTo(CarbonImmutable::parse('2026-11-06 12:00:00'));
    $this->usuario = User::create([
        'nombre' => 'Prueba reportes', 'email' => 'reportes@example.test',
        'contrasena' => 'secret', 'rol' => 'Administrador', 'activo' => true,
    ]);
    $this->venta = DB::table('tipo_salida')->insertGetId(['nombre' => 'Venta'], 'idTipo');
    $this->merma = DB::table('tipo_salida')->insertGetId(['nombre' => 'Merma por Daño'], 'idTipo');
    DB::table('producto')->insert(['codigo' => 'HELADO', 'nombre' => 'Helado de prueba']);
});

function salidaReporte(int $usuario, int $tipo, string $fecha, array $lineas, bool $eliminada = false): int
{
    $id = DB::table('salida')->insertGetId([
        'fecha' => $fecha, 'idTipo' => $tipo, 'idUsuario' => $usuario,
        'totalSalida' => 99999, 'deleted_at' => $eliminada ? now() : null,
    ], 'idSalida');
    foreach ($lineas as [$cantidad, $precio, $eliminado]) {
        DB::table('detalle_salida')->insert([
            'idSalida' => $id, 'codigoProducto' => 'HELADO', 'cantidad' => $cantidad,
            'precioCobrado' => $precio, 'deleted_at' => $eliminado ? now() : null,
        ]);
    }

    return $id;
}

test('reportes requiere autenticacion', function () {
    $this->get('/reportes')->assertRedirect(route('login'));
    $this->get('/reportes?exportar=csv')->assertRedirect(route('login'));
});

test('reportes y su exportacion respetan el acceso exclusivo de administradores', function (string $url) {
    $this->usuario->update(['rol' => 'Cajero']);

    $this->actingAs($this->usuario)->get($url)->assertNotFound();
})->with(['/reportes', '/reportes?exportar=csv']);

test('ventas usa precios cobrados y cuenta tickets sin duplicar lineas ni incluir mermas o eliminados', function () {
    salidaReporte($this->usuario->idUsuario, $this->venta, '2026-11-01 00:00:00', [[2, 800, false], [1, 500, false], [9, 9000, true]]);
    salidaReporte($this->usuario->idUsuario, $this->venta, '2026-11-06 23:59:59', [[1, 400, false]]);
    salidaReporte($this->usuario->idUsuario, $this->merma, '2026-11-03 10:00:00', [[5, 1000, false]]);
    salidaReporte($this->usuario->idUsuario, $this->venta, '2026-11-03 10:00:00', [[5, 1000, false]], true);
    salidaReporte($this->usuario->idUsuario, $this->venta, '2026-10-31 23:59:59', [[5, 1000, false]]);

    $this->actingAs($this->usuario)->get('/reportes?fecha_inicio=2026-11-01&fecha_fin=2026-11-06')
        ->assertOk()->assertSee('Helado de prueba')->assertSee('Sin categoría')
        ->assertViewHas('datos', fn ($datos) => $datos['ingresos'] === 2500.0
            && $datos['ventas'] === 2 && (int) $datos['unidades'] === 4
            && $datos['ticket'] === 1250.0 && $datos['dias']->count() === 6);
});

test('habitos incluye ceros y compara el promedio por ocurrencias de cada dia', function () {
    salidaReporte($this->usuario->idUsuario, $this->venta, '2026-11-02 18:15:00', [[1, 1200, false], [1, 800, false]]);
    $this->actingAs($this->usuario)->get('/reportes?seccion=habitos&fecha_inicio=2026-11-01&fecha_fin=2026-11-09')
        ->assertOk()->assertSee('Actividad por día y hora')
        ->assertViewHas('datos', fn ($datos) => $datos['mapa'][0][18] === 1
            && $datos['maximo'] === 1 && $datos['horaPunta'] === 18
            && $datos['semana'][0]['ocurrencias'] === 2 && $datos['semana'][0]['promedio'] === 1000.0
            && $datos['diaFuerte']['nombre'] === 'Lunes' && $datos['diaDebil']['promedio'] === 0);
});

test('inventario descuenta salidas de todos los tipos y no repite el stock en cada vencimiento', function () {
    $proveedor = DB::table('proveedor')->insertGetId(['nombre' => 'Proveedor de prueba'], 'idProveedor');
    $ingreso = DB::table('ingreso')->insertGetId([
        'fecha' => '2026-11-01 09:00:00', 'idProveedor' => $proveedor,
        'idUsuario' => $this->usuario->idUsuario, 'totalCompra' => 10000,
    ], 'idIngreso');
    foreach ([['2026-11-07', 5], ['2026-11-10', 10]] as [$fecha, $cantidad]) {
        DB::table('detalle_ingreso')->insert([
            'idIngreso' => $ingreso, 'codigoProducto' => 'HELADO',
            'cantidad' => $cantidad, 'precioCompra' => 500, 'fechaVencimiento' => $fecha,
        ]);
    }
    DB::table('lista_precio')->insert([
        'codigoProducto' => 'HELADO', 'precioVenta' => 1000, 'fechaInicio' => '2026-11-01 00:00:00',
    ]);
    salidaReporte($this->usuario->idUsuario, $this->venta, '2026-11-02 10:00:00', [[4, 800, false]]);
    salidaReporte($this->usuario->idUsuario, $this->merma, '2026-11-03 10:00:00', [[3, 0, false]]);
    salidaReporte($this->usuario->idUsuario, $this->venta, '2026-11-03 11:00:00', [[5, 1000, false]], true);

    $this->actingAs($this->usuario)->get('/reportes?seccion=inventario')
        ->assertOk()->assertSee('Cantidades estimadas')
        ->assertViewHas('datos', fn ($datos) => $datos['bajoStock']->first()->stock === 8
            && $datos['reposicion'] === 12 && $datos['lotes']->count() === 1
            && $datos['lotes']->first()->cantidad === 8 && $datos['valorRiesgo'] == 8000);
});

test('reportes muestra estados vacios y exporta el mismo filtro con proteccion de formulas', function () {
    foreach (['ventas', 'inventario', 'habitos'] as $seccion) {
        $this->actingAs($this->usuario)->get('/reportes?seccion='.$seccion)->assertOk();
    }
    DB::table('producto')->where('codigo', 'HELADO')->update(['nombre' => '=1+1']);
    salidaReporte($this->usuario->idUsuario, $this->venta, '2026-11-02 10:00:00', [[2, 800, false]]);
    $respuesta = $this->actingAs($this->usuario)
        ->get('/reportes?fecha_inicio=2026-11-02&fecha_fin=2026-11-02&exportar=csv')
        ->assertOk()->assertDownload('reportes-ventas-2026-11-02-2026-11-02.csv');
    expect($respuesta->streamedContent())->toContain("'=1+1", '1600', '2026-11-02');
});

test('inventario conserva alertas vencidas y avisa cuando no hay precio vigente', function () {
    $proveedor = DB::table('proveedor')->insertGetId(['nombre' => 'Proveedor'], 'idProveedor');
    $ingreso = DB::table('ingreso')->insertGetId([
        'fecha' => '2026-11-01 09:00:00', 'idProveedor' => $proveedor,
        'idUsuario' => $this->usuario->idUsuario, 'totalCompra' => 1000,
    ], 'idIngreso');
    DB::table('detalle_ingreso')->insert([
        'idIngreso' => $ingreso, 'codigoProducto' => 'HELADO', 'cantidad' => 2,
        'precioCompra' => 500, 'fechaVencimiento' => '2026-11-05',
    ]);
    $this->actingAs($this->usuario)->get('/reportes?seccion=inventario')
        ->assertOk()->assertSee('Vencido')->assertSee('Sin precio')
        ->assertViewHas('datos', fn ($datos) => $datos['lotes']->first()->dias === -1
            && $datos['sinPrecio'] === 1 && $datos['valorRiesgo'] == 0);

    $csv = $this->actingAs($this->usuario)->get('/reportes?seccion=inventario&exportar=csv')->assertOk();
    expect($csv->streamedContent())->toContain('FEFO', '2026-11-05', 'HELADO');
});

test('exportacion de habitos conserva cada ticket una sola vez', function () {
    salidaReporte($this->usuario->idUsuario, $this->venta, '2026-11-02 18:30:00', [[1, 800, false], [2, 800, false]]);
    $csv = $this->actingAs($this->usuario)->get('/reportes?seccion=habitos&fecha_inicio=2026-11-02&fecha_fin=2026-11-02&exportar=csv')->assertOk();
    expect($csv->streamedContent())->toContain('Lunes;18:00;1', 'Lunes;17:00;0');
});

test('rechaza fechas invalidas rangos invertidos demasiado largos y secciones desconocidas', function (array $filtros) {
    $this->actingAs($this->usuario)->getJson('/reportes?'.http_build_query($filtros))->assertUnprocessable();
})->with([
    [['fecha_inicio' => '2026-02-30']],
    [['fecha_inicio' => '2026-11-06', 'fecha_fin' => '2026-11-01']],
    [['fecha_inicio' => '2025-01-01', 'fecha_fin' => '2026-11-06']],
    [['seccion' => 'desconocida']],
]);
