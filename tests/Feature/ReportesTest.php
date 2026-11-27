<?php

use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

beforeEach(function () {
    Artisan::call('migrate:fresh');
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

test('ventas usa precios cobrados y cuenta salidas sin duplicar lineas ni incluir mermas o eliminados', function () {
    salidaReporte($this->usuario->idUsuario, $this->venta, '2026-11-01 00:00:00', [[2, 800, false], [1, 500, false], [9, 9000, true]]);
    salidaReporte($this->usuario->idUsuario, $this->venta, '2026-11-06 23:59:59', [[1, 400, false]]);
    salidaReporte($this->usuario->idUsuario, $this->merma, '2026-11-03 10:00:00', [[5, 1000, false]]);
    salidaReporte($this->usuario->idUsuario, $this->venta, '2026-11-03 10:00:00', [[5, 1000, false]], true);
    salidaReporte($this->usuario->idUsuario, $this->venta, '2026-10-31 23:59:59', [[5, 1000, false]]);

    $this->actingAs($this->usuario)->get('/reportes?fecha_inicio=2026-11-01&fecha_fin=2026-11-06')
        ->assertOk()->assertSee('Helado de prueba')->assertSee('Sin categoría')
        ->assertViewHas('datos', fn ($datos) => $datos['ingresos'] === 2500.0
            && $datos['ventas'] === 2 && (int) $datos['unidades'] === 4
            && $datos['ingresoPromedioPorSalida'] === 1250.0 && $datos['dias']->count() === 6);
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

    $response = $this->actingAs($this->usuario)->get('/reportes?seccion=inventario')
        ->assertOk()->assertDontSee('Riesgo por vencimiento');
    $datos = $response->viewData('datos');
    expect($datos['bajoStock']->first()->stock)->toBe(8)
        ->and($datos['reposicion'])->toBe(12);
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

test('inventario y csv no muestran fechas de productos', function () {
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
        ->assertOk()->assertDontSee('Vencido')->assertDontSee('Vencimientos para revisar');

    $csv = $this->actingAs($this->usuario)->get('/reportes?seccion=inventario&exportar=csv')->assertOk();
    expect($csv->streamedContent())->toContain('HELADO')->not->toContain('FEFO', '2026-11-05');
});

test('exportacion de habitos conserva cada salida una sola vez', function () {
    salidaReporte($this->usuario->idUsuario, $this->venta, '2026-11-02 18:30:00', [[1, 800, false], [2, 800, false]]);
    $csv = $this->actingAs($this->usuario)->get('/reportes?seccion=habitos&fecha_inicio=2026-11-02&fecha_fin=2026-11-02&exportar=csv')->assertOk();
    expect($csv->streamedContent())->toContain('Lunes;18:00;1', 'Lunes;17:00;0');
});

test('pantallas y exportaciones de reportes cuentan salidas con vocabulario uniforme', function (string $seccion) {
    salidaReporte($this->usuario->idUsuario, $this->venta, '2026-11-02 18:30:00', [[1, 800, false], [2, 800, false]]);
    $consulta = http_build_query(['seccion' => $seccion, 'fecha_inicio' => '2026-11-02', 'fecha_fin' => '2026-11-02', 'dia' => '2026-11-02']);
    $pantalla = $this->actingAs($this->usuario)->get('/reportes?'.$consulta)->assertOk();
    $csv = $this->get('/reportes?'.$consulta.'&exportar=csv')->assertOk()->streamedContent();
    foreach ([$pantalla->getContent(), $csv] as $contenido) {
        expect($contenido)->not->toMatch('/\b(?:tickets?|transacci(?:ón|ones))\b/iu');
    }
    if ($seccion !== 'inventario') {
        $pantalla->assertSee('Salidas');
        expect($csv)->toContain('Salidas');
    }
    if (in_array($seccion, ['ventas', 'calendario', 'fechas'], true)) {
        $pantalla->assertSee('Ingreso promedio por salida')
            ->assertViewHas('datos', fn ($datos) => $datos['ventas'] === 1 && $datos['ingresoPromedioPorSalida'] === 2400.0);
        expect($csv)->toContain('Ingreso promedio por salida CLP');
    }
})->with(['ventas', 'inventario', 'habitos', 'calendario']);

test('acepta rangos de hasta diez años', function (array $filtros) {
    $this->actingAs($this->usuario)->get('/reportes?'.http_build_query($filtros))->assertOk();
})->with([
    [['fecha_inicio' => '2025-01-01', 'fecha_fin' => '2026-11-06']],
    [['fecha_inicio' => '2016-11-07', 'fecha_fin' => '2026-11-06']],
]);

test('rechaza fechas invalidas rangos invertidos demasiado largos y secciones desconocidas', function (array $filtros) {
    $this->actingAs($this->usuario)->getJson('/reportes?'.http_build_query($filtros))->assertUnprocessable();
})->with([
    [['fecha_inicio' => '2026-02-30']],
    [['fecha_inicio' => '2026-11-06', 'fecha_fin' => '2026-11-01']],
    [['fecha_inicio' => '2016-11-06', 'fecha_fin' => '2026-11-06']],
    [['seccion' => 'desconocida']],
]);

function ingresoProveedorReporte(int $usuario, int $proveedor, bool $ingresoEliminado = false, bool $detalleEliminado = false): void
{
    $id = DB::table('ingreso')->insertGetId([
        'fecha' => '2026-11-01 09:00:00', 'idProveedor' => $proveedor,
        'idUsuario' => $usuario, 'totalCompra' => 500, 'deleted_at' => $ingresoEliminado ? now() : null,
    ], 'idIngreso');
    DB::table('detalle_ingreso')->insert([
        'idIngreso' => $id, 'codigoProducto' => 'HELADO', 'cantidad' => 1,
        'precioCompra' => 500, 'fechaVencimiento' => '2026-12-01', 'deleted_at' => $detalleEliminado ? now() : null,
    ]);
}

test('sugerencias muestra proveedores con historial valido y prepara cantidades editadas', function () {
    $uno = DB::table('proveedor')->insertGetId(['nombre' => 'Proveedor Uno', 'telefono' => '+56912345678', 'correo' => 'uno@example.test'], 'idProveedor');
    $dos = DB::table('proveedor')->insertGetId(['nombre' => 'Proveedor Dos'], 'idProveedor');
    ingresoProveedorReporte($this->usuario->idUsuario, $uno);
    ingresoProveedorReporte($this->usuario->idUsuario, $dos);
    DB::table('producto')->insert(['codigo' => 'SIN', 'nombre' => 'Sin historial']);
    $this->actingAs($this->usuario)->get('/reportes?seccion=inventario')
        ->assertOk()->assertSee('Proveedor Uno')->assertSee('Proveedor Dos')->assertSee('Sin proveedor asociado');
    $this->post('/reportes/solicitudes', ['productos' => [
        ['codigo' => 'HELADO', 'proveedor' => $uno, 'cantidad' => 7],
        ['codigo' => 'SIN', 'proveedor' => '', 'cantidad' => 20],
    ]])->assertOk()->assertSee('7 unidades')->assertSee('Abrir WhatsApp')->assertSee('Redactar correo')->assertDontSee('Sin historial');
    $this->post('/reportes/solicitudes', ['productos' => [
        ['codigo' => 'HELADO', 'proveedor' => $dos, 'cantidad' => 5],
    ]])->assertOk()->assertSee('Sin teléfono válido')->assertSee('Sin correo válido');
});

test('sugerencias rechaza proveedor sin historial o con historial eliminado', function () {
    $sin = DB::table('proveedor')->insertGetId(['nombre' => 'Sin historial'], 'idProveedor');
    $eliminado = DB::table('proveedor')->insertGetId(['nombre' => 'Ingreso eliminado'], 'idProveedor');
    $detalle = DB::table('proveedor')->insertGetId(['nombre' => 'Detalle eliminado'], 'idProveedor');
    ingresoProveedorReporte($this->usuario->idUsuario, $eliminado, true);
    ingresoProveedorReporte($this->usuario->idUsuario, $detalle, false, true);
    foreach ([$sin, $eliminado, $detalle] as $id) {
        $this->actingAs($this->usuario)->postJson('/reportes/solicitudes', ['productos' => [
            ['codigo' => 'HELADO', 'proveedor' => $id, 'cantidad' => 2],
        ]])->assertUnprocessable();
    }
    $this->actingAs($this->usuario)->postJson('/reportes/solicitudes', ['productos' => [
        ['codigo' => 'HELADO', 'proveedor' => null, 'cantidad' => 2],
    ]])->assertUnprocessable();
});

test('sugerencias protege acceso y valida cantidades y estado vacio', function () {
    $this->post('/reportes/solicitudes')->assertRedirect(route('login'));
    $this->usuario->update(['rol' => 'Cajero']);
    $this->actingAs($this->usuario)->post('/reportes/solicitudes')->assertNotFound();
    $this->usuario->update(['rol' => 'Administrador']);
    $this->postJson('/reportes/solicitudes', ['productos' => [['codigo' => 'HELADO', 'proveedor' => 1, 'cantidad' => 0]]])->assertUnprocessable();
    DB::table('producto')->where('codigo', 'HELADO')->update(['deleted_at' => now()]);
    $this->get('/reportes?seccion=inventario')->assertOk()->assertSee('No hay productos que reponer.');
});

test('sugerencias rechaza proveedor o producto eliminados aunque tengan historial', function () {
    $proveedor = DB::table('proveedor')->insertGetId(['nombre' => 'Proveedor'], 'idProveedor');
    ingresoProveedorReporte($this->usuario->idUsuario, $proveedor);
    $payload = ['productos' => [['codigo' => 'HELADO', 'proveedor' => $proveedor, 'cantidad' => 2]]];
    DB::table('proveedor')->where('idProveedor', $proveedor)->update(['deleted_at' => now()]);
    $this->actingAs($this->usuario)->postJson('/reportes/solicitudes', $payload)->assertUnprocessable();
    DB::table('proveedor')->where('idProveedor', $proveedor)->update(['deleted_at' => null]);
    DB::table('producto')->where('codigo', 'HELADO')->update(['deleted_at' => now()]);
    $this->postJson('/reportes/solicitudes', $payload)->assertUnprocessable();
});

test('se preparan solicitudes separadas y se omite producto sin proveedor', function () {
    $uno = DB::table('proveedor')->insertGetId(['nombre' => 'Proveedor Uno'], 'idProveedor');
    $dos = DB::table('proveedor')->insertGetId(['nombre' => 'Proveedor Dos'], 'idProveedor');
    ingresoProveedorReporte($this->usuario->idUsuario, $uno);
    DB::table('producto')->insert([['codigo' => 'OTRO', 'nombre' => 'Otro producto'], ['codigo' => 'SIN', 'nombre' => 'Sin proveedor']]);
    $ingreso = DB::table('ingreso')->insertGetId(['fecha' => now(), 'idProveedor' => $dos, 'idUsuario' => $this->usuario->idUsuario, 'totalCompra' => 100], 'idIngreso');
    DB::table('detalle_ingreso')->insert(['idIngreso' => $ingreso, 'codigoProducto' => 'OTRO', 'cantidad' => 1, 'precioCompra' => 100]);
    $this->actingAs($this->usuario)->post('/reportes/solicitudes', ['productos' => [
        ['codigo' => 'HELADO', 'proveedor' => $uno, 'cantidad' => 4],
        ['codigo' => 'OTRO', 'proveedor' => $dos, 'cantidad' => 6],
        ['codigo' => 'SIN', 'proveedor' => '', 'cantidad' => 20],
    ]])->assertOk()->assertSee('Proveedor Uno')->assertSee('Proveedor Dos')->assertSee('4 unidades')->assertSee('6 unidades')->assertDontSee('Sin proveedor');
});
