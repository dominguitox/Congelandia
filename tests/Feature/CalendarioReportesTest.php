<?php

use App\Models\User;
use App\Services\Reportes\AnalisisFechasService;
use App\Services\Reportes\CalendarioReporteService;
use App\Services\Reportes\FeriadosChile;
use App\Services\Reportes\PronosticoStockService;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);

beforeEach(function () {
    config(['app.key' => 'base64:'.base64_encode(str_repeat('c', 32))]);
    $this->withoutVite();
    $this->travelTo(CarbonImmutable::parse('2026-11-06 12:00:00'));
    $this->admin = User::create(['nombre' => 'Calendario', 'email' => 'calendario@example.test', 'contrasena' => 'secret', 'rol' => 'Administrador', 'activo' => true]);
    $this->venta = DB::table('tipo_salida')->insertGetId(['nombre' => ' Venta '], 'idTipo');
    $this->merma = DB::table('tipo_salida')->insertGetId(['nombre' => 'Merma por Daño'], 'idTipo');
    $this->categoria = DB::table('categoria')->insertGetId(['nombre' => 'Congelados'], 'idCategoria');
    DB::table('producto')->insert([
        ['codigo' => 'A', 'nombre' => 'Helado A', 'idCategoria' => $this->categoria],
        ['codigo' => 'B', 'nombre' => 'Helado B', 'idCategoria' => $this->categoria],
    ]);
    $this->proveedor = DB::table('proveedor')->insertGetId(['nombre' => 'Proveedor A'], 'idProveedor');
});

function ventaCalendario($test, string $fecha, int $cantidad = 1, float $precio = 1000, string $codigo = 'A', ?int $tipo = null, bool $borrada = false, bool $detalleBorrado = false): int
{
    $id = DB::table('salida')->insertGetId(['fecha' => $fecha, 'idTipo' => $tipo ?? $test->venta, 'idUsuario' => $test->admin->idUsuario, 'totalSalida' => 999999, 'deleted_at' => $borrada ? now() : null], 'idSalida');
    DB::table('detalle_salida')->insert(['idSalida' => $id, 'codigoProducto' => $codigo, 'cantidad' => $cantidad, 'precioCobrado' => $precio, 'deleted_at' => $detalleBorrado ? now() : null]);

    return $id;
}

function ingresoCalendario($test, string $fecha, int $cantidad = 10, float $precio = 100, string $codigo = 'A', bool $borrado = false, bool $detalleBorrado = false, bool $sinProveedor = false): int
{
    $id = DB::table('ingreso')->insertGetId(['fecha' => $fecha, 'idProveedor' => $sinProveedor ? null : $test->proveedor, 'idUsuario' => $test->admin->idUsuario, 'totalCompra' => $cantidad * $precio, 'deleted_at' => $borrado ? now() : null], 'idIngreso');
    DB::table('detalle_ingreso')->insert(['idIngreso' => $id, 'codigoProducto' => $codigo, 'cantidad' => $cantidad, 'precioCompra' => $precio, 'fechaVencimiento' => '2030-12-31', 'deleted_at' => $detalleBorrado ? now() : null]);

    return $id;
}

function calendarioPeriodo(string $fecha, string $vista = 'mes'): array
{
    return app(CalendarioReporteService::class)->periodo(CarbonImmutable::parse($fecha), $vista, app(FeriadosChile::class), app(PronosticoStockService::class));
}

test('calendario y fechas conservan autorizacion administrativa', function (string $seccion) {
    $this->get('/reportes?seccion='.$seccion)->assertRedirect(route('login'));
    $this->admin->update(['rol' => 'Cajero']);
    $this->actingAs($this->admin)->get('/reportes?seccion='.$seccion)->assertNotFound();
})->with(['calendario', 'fechas']);

test('mes completo incluye adyacentes y semana comienza en lunes', function () {
    $mes = calendarioPeriodo('2026-11-15');
    expect($mes['desde']->toDateString())->toBe('2026-10-26')
        ->and($mes['hasta']->toDateString())->toBe('2026-12-06')
        ->and(count($mes['dias']))->toBe(42)
        ->and($mes['dias'][0]['adyacente'])->toBeTrue();
    $semana = calendarioPeriodo('2026-11-06', 'semana');
    expect($semana['desde']->toDateString())->toBe('2026-11-02')->and(count($semana['dias']))->toBe(7);
    $this->actingAs($this->admin)->get('/reportes?seccion=calendario')->assertOk()->assertSee('Período anterior')->assertSee('Semana')->assertSee('Hoy')->assertViewHas('vista', 'mes');
});

test('ofertas se expanden inclusivamente por periodo y distinguen vencidas programadas y pausadas', function () {
    foreach ([['2026-10-31 12:00:00', '2026-11-02 10:00:00', true, false], ['2026-11-08 12:00:00', '2026-11-12 10:00:00', true, false], ['2026-11-01 12:00:00', '2026-11-02 10:00:00', false, false], ['2026-11-01 12:00:00', '2026-11-02 10:00:00', true, true]] as [$inicio, $fin, $activa, $borrada]) {
        DB::table('promocion')->insert(['codigoProducto' => 'A', 'porcentajeDescuento' => 12.5, 'fechaInicio' => $inicio, 'fechaFin' => $fin, 'activa' => $activa, 'deleted_at' => $borrada ? now() : null]);
    }
    $dias = collect(calendarioPeriodo('2026-11-01')['dias']);
    $noviembre = $dias->first(fn ($d) => $d['fecha']->toDateString() === '2026-11-01');
    expect($noviembre['ofertas'])->toHaveCount(2)
        ->and($noviembre['ofertas']->pluck('estado')->all())->toBe(['Vencida', 'Pausada (no válida)']);
    expect($dias->first(fn ($d) => $d['fecha']->toDateString() === '2026-11-02')['ofertas'])->toHaveCount(2)
        ->and($dias->first(fn ($d) => $d['fecha']->toDateString() === '2026-11-08')['ofertas']->first()->estado)->toBe('Programada');
    $this->actingAs($this->admin)->get('/reportes?seccion=calendario&fecha_calendario=2026-11-01')->assertOk()->assertSee('12,50%')->assertSee('Pausada (no válida)');
});

test('oferta conserva tono por identificador entre dias meses semanas nuevas consultas y pausa', function () {
    $id = DB::table('promocion')->insertGetId(['codigoProducto' => 'A', 'porcentajeDescuento' => 15, 'fechaInicio' => '2026-10-30 12:00:00', 'fechaFin' => '2026-12-02 18:00:00', 'activa' => true], 'idPromocion');
    $tono = null;
    foreach ([['2026-10-30', 'mes'], ['2026-11-15', 'mes'], ['2026-12-01', 'mes'], ['2026-11-30', 'semana'], ['2026-11-15', 'mes']] as [$fecha, $vista]) {
        $eventos = collect(calendarioPeriodo($fecha, $vista)['dias'])->flatMap(fn ($dia) => $dia['ofertas']);
        $tono ??= $eventos->first()->tono;
        expect($eventos->pluck('tono')->unique()->all())->toBe([$tono]);
    }
    // Otro evento con fecha anterior cambia el orden de consulta, sin cambiar la identidad visual.
    DB::table('promocion')->insert(['codigoProducto' => 'B', 'porcentajeDescuento' => 20, 'fechaInicio' => '2026-10-29 12:00:00', 'fechaFin' => '2026-12-03 18:00:00', 'activa' => true]);
    $celda = collect(calendarioPeriodo('2026-11-01')['dias'])->first(fn ($dia) => $dia['fecha']->toDateString() === '2026-11-01');
    expect($celda['ofertas']->pluck('tono')->unique())->toHaveCount(2)
        ->and($celda['ofertas']->firstWhere('idPromocion', $id)->tono)->toBe($tono);
    DB::table('promocion')->where('idPromocion', $id)->update(['activa' => false]);
    $this->actingAs($this->admin)->get('/reportes?seccion=calendario&fecha_calendario=2026-11-01')
        ->assertOk()->assertSee('Pausada (no válida)')
        ->assertSee('data-oferta-id="'.$id.'" style="--oferta-tono: '.$tono.'"', false)
        ->assertViewHas('calendario', function ($calendario) use ($id, $tono) {
            $eventos = collect($calendario['dias'])->flatMap(fn ($dia) => $dia['ofertas'])->where('idPromocion', $id);

            return $eventos->isNotEmpty() && $eventos->every(fn ($oferta) => $oferta->tono === $tono && $oferta->estado === 'Pausada (no válida)');
        });
});

test('ofertas identifican inicio continuacion termino y oferta del mismo dia sin confundir limites de vista', function () {
    $id = DB::table('promocion')->insertGetId(['codigoProducto' => 'A', 'porcentajeDescuento' => 15, 'fechaInicio' => '2026-10-31 12:00:00', 'fechaFin' => '2026-11-03 18:00:00', 'activa' => true], 'idPromocion');
    $corta = DB::table('promocion')->insertGetId(['codigoProducto' => 'B', 'porcentajeDescuento' => 20, 'fechaInicio' => '2026-11-01 09:00:00', 'fechaFin' => '2026-11-01 10:00:00', 'activa' => true], 'idPromocion');
    $dias = collect(calendarioPeriodo('2026-11-01')['dias'])->keyBy(fn ($dia) => $dia['fecha']->toDateString());
    expect($dias['2026-10-31']['ofertas']->firstWhere('idPromocion', $id)->tramo)->toBe('Comienza hoy')
        ->and($dias['2026-11-01']['ofertas']->firstWhere('idPromocion', $id)->tramo)->toBeNull()
        ->and($dias['2026-11-02']['ofertas']->firstWhere('idPromocion', $id)->tramo)->toBeNull()
        ->and($dias['2026-11-03']['ofertas']->firstWhere('idPromocion', $id)->tramo)->toBe('Termina hoy')
        ->and($dias['2026-11-01']['ofertas']->firstWhere('idPromocion', $corta)->tramo)->toBe('Comienza y termina hoy')
        ->and($dias['2026-11-04']['ofertas'])->toBeEmpty();
    $semana = collect(calendarioPeriodo('2026-11-02', 'semana')['dias']);
    expect($semana->first()['ofertas']->firstWhere('idPromocion', $id)->tramo)->toBeNull();
    $respuesta = $this->actingAs($this->admin)->get('/reportes?seccion=calendario&fecha_calendario=2026-11-01')->assertOk();
    $documento = new DOMDocument;
    $documento->loadHTML('<?xml encoding="utf-8" ?>'.$respuesta->getContent(), LIBXML_NOERROR | LIBXML_NOWARNING);
    $xpath = new DOMXPath($documento);
    $estilos = [];
    foreach (['2026-10-31' => 'Comienza hoy', '2026-11-01' => null, '2026-11-03' => 'Termina hoy'] as $fecha => $tramo) {
        $evento = $xpath->query('//td[.//time[@datetime="'.$fecha.'"]]//details[@data-oferta-id="'.$id.'"]')->item(0);
        expect($evento)->not->toBeNull();
        expect($evento->textContent)->toContain('Oferta #'.$id);
        if ($tramo !== null) {
            expect($evento->textContent)->toContain($tramo);
        } else {
            expect($xpath->query('.//span[@class="reportes-oferta-tramo"]', $evento)->length)->toBe(0);
        }
        $estilos[] = $evento->getAttribute('style');
    }
    expect(array_unique($estilos))->toHaveCount(1);
    $respuesta->assertSee('Comienza y termina hoy')->assertDontSee('Continúa');
});

test('feriados nacionales locales incluyen variables y extraordinarios sin regionales ni comerciales', function () {
    $servicio = app(FeriadosChile::class);
    $fechas = fn ($anio) => collect($servicio->entre(CarbonImmutable::parse("$anio-01-01"), CarbonImmutable::parse("$anio-12-31")));
    expect($fechas(2025))->toHaveCount(19);
    expect($fechas(2025)->pluck('fecha')->all())->toContain('2025-04-18', '2025-04-19', '2025-06-20', '2025-11-16', '2025-12-14');
    expect($fechas(2026))->toHaveCount(16);
    expect($fechas(2026)->pluck('fecha')->all())->toContain('2026-04-03', '2026-04-04', '2026-06-21')->not->toContain('2026-06-07', '2026-08-20', '2026-12-31', '2026-11-27');
    expect($fechas(2027)->pluck('fecha')->all())->toContain('2027-03-26', '2027-03-27', '2027-06-28', '2027-10-11', '2027-09-17');
    expect($fechas(2024))->toBeEmpty()->and(calendarioPeriodo('2024-06-01')['sinCobertura'])->toBe([2024]);
    // Cobertura ampliada explícitamente: comprueba reglas de traslado sin afirmar un catálogo completo de esos años.
    config(['feriados.solsticios' => [2023 => '06-21', 2029 => '06-21']]);
    expect($fechas(2023)->pluck('fecha')->all())->toContain('2023-01-02', '2023-10-27');
    expect($fechas(2029)->pluck('fecha')->all())->toContain('2029-09-17', '2029-11-02');
});

test('seleccion de dia conserva rango permite aplicar filtro limpiar y detallar precios cobrados', function () {
    ventaCalendario($this, '2026-11-01 23:59:59', 2, 700);
    ventaCalendario($this, '2026-11-02 00:00:00', 10, 900);
    $this->actingAs($this->admin)->get('/reportes?seccion=calendario&fecha_inicio=2026-10-01&fecha_fin=2026-10-31&dia=2026-11-01')
        ->assertOk()->assertSee('Aplicar esta fecha al filtro de reportes')->assertSee('Limpiar selección y volver al rango')
        ->assertViewHas('fechaInicio', '2026-10-01')
        ->assertViewHas('detalleDia', fn ($d) => $d->total() === 1 && (float) $d->first()->precioCobrado === 700.0)
        ->assertViewHas('analisis', fn ($a) => (float) $a['ocurrencias']->sum('ingresos') === 1400.0);
    $this->get('/reportes?fecha_inicio=2026-11-01&fecha_fin=2026-11-01')->assertOk()->assertViewHas('datos', fn ($d) => $d['ingresos'] === 1400.0);
    $this->get('/reportes?seccion=calendario&fecha_inicio=2026-10-01&fecha_fin=2026-10-31')->assertOk()->assertViewHas('dia', null);
});

test('historial pondera costos por ocurrencia precios y categorias sin promediar promedios', function () {
    ingresoCalendario($this, '2024-11-14 10:00:00', 10, 100);
    ingresoCalendario($this, '2025-11-15 23:59:59', 30, 300); // Se incluye todo el mismo día: costo 250.
    ingresoCalendario($this, '2025-11-16 00:00:00', 1000, 9000); // Nunca contamina 2025-11-15.
    ingresoCalendario($this, '2024-11-14 10:00:00', 9999, 9999, 'A', true);
    ingresoCalendario($this, '2024-11-14 10:00:00', 9999, 9999, 'A', false, true);
    ventaCalendario($this, '2024-11-15 12:00:00', 2, 1000);
    ventaCalendario($this, '2025-11-15 12:00:00', 6, 500);
    ingresoCalendario($this, '2024-11-14 10:00:00', 20, 200, 'B');
    ventaCalendario($this, '2025-11-15 12:00:00', 2, 800, 'B');
    $analisis = app(AnalisisFechasService::class)->explorar(2025, '11-15', null);
    $producto = $analisis['rankings']['productos']->first();
    expect($producto['codigo'])->toBe('A')->and($producto['precio'])->toBe(625.0)
        ->and($producto['costo'])->toBe(212.5)->and($producto['margen'])->toBe(412.5)
        ->and($producto['ocurrencias'])->toBe(2);
    $categoria = $analisis['rankings']['categorias']->first();
    expect($categoria['unidades'])->toBe(10)->and($categoria['precio'])->toBe(660.0)
        ->and($categoria['costo'])->toBe(210.0)->and($categoria['margen'])->toBe(450.0)
        ->and($categoria['ocurrencias'])->toBe(2);
    expect($analisis['recurrentes']->first()->ocurrencias)->toBe(2)
        ->and((int) $analisis['recurrentes']->first()->ventas)->toBe(3);
    $this->actingAs($this->admin)->get('/reportes?seccion=fechas&patron=11-15')->assertOk()->assertSee('Margen unitario estimado')->assertSee('2024');
});

test('costos ausentes no se tratan como cero ni generan margenes inventados', function () {
    ingresoCalendario($this, '2025-11-15 09:00:00', 10, 300);
    ventaCalendario($this, '2024-11-15 12:00:00', 2, 1000);
    ventaCalendario($this, '2025-11-15 12:00:00', 6, 500);
    $ranking = app(AnalisisFechasService::class)->explorar(2025, '11-15', null)['rankings'];
    expect($ranking['productos']->first()['costo'])->toBeNull()->and($ranking['productos']->first()['margen'])->toBeNull()
        ->and($ranking['productos']->first()['sinCosto'])->toBe(2)->and($ranking['categorias']->first()['costo'])->toBeNull();
    $this->actingAs($this->admin)->get('/reportes?seccion=fechas&patron=11-15')->assertOk()->assertSee('Sin costo histórico completo')->assertSee('No estimable');
});

test('fechas ordenan ingresos rankings ordenan unidades y excluyen mermas y anulaciones logicas', function () {
    ventaCalendario($this, '2026-11-01 12:00:00', 2, 1000);
    ventaCalendario($this, '2026-11-02 12:00:00', 3, 100, 'B');
    ventaCalendario($this, '2026-11-01 12:00:00', 100, 999, 'A', $this->merma);
    ventaCalendario($this, '2026-11-01 12:00:00', 100, 999, 'A', null, true);
    ventaCalendario($this, '2026-11-01 12:00:00', 100, 999, 'A', null, false, true);
    DB::table('producto')->where('codigo', 'A')->update(['deleted_at' => now()]);
    $analisis = app(AnalisisFechasService::class)->explorar(2026, null, '2026-11-01');
    expect($analisis['exactas']->first()->fecha)->toBe('2026-11-01')->and((int) $analisis['exactas']->sum('ventas'))->toBe(2)
        ->and($analisis['rankings']['productos']->first()['unidades'])->toBe(2);
    $dia = collect(calendarioPeriodo('2026-11-01')['dias'])->first(fn ($d) => $d['fecha']->toDateString() === '2026-11-01');
    expect((float) $dia['ventas']->ingresos)->toBe(2000.0)->and((int) $dia['ventas']->ventas)->toBe(1);
});

test('pronostico usa stock real observado mermas y contexto de ingresos sin cantidades sugeridas', function () {
    ingresoCalendario($this, '2026-09-01 09:00:00', 100);
    ingresoCalendario($this, '2026-10-01 09:00:00', 20);
    ingresoCalendario($this, '2026-12-01 09:00:00', 9999);
    ingresoCalendario($this, '2026-10-01 09:00:00', 9999, 100, 'A', true);
    ingresoCalendario($this, '2026-10-01 09:00:00', 9999, 100, 'A', false, true);
    foreach (['2026-10-10', '2026-10-20', '2026-11-01'] as $fecha) {
        ventaCalendario($this, $fecha.' 12:00:00', 20);
    }
    ventaCalendario($this, '2026-11-02 12:00:00', 50, 0, 'A', $this->merma);
    ventaCalendario($this, '2026-11-02 12:00:00', 999, 0, 'A', null, true);
    ventaCalendario($this, '2026-11-02 12:00:00', 999, 0, 'A', null, false, true);
    ventaCalendario($this, '2026-12-02 12:00:00', 999);
    $aviso = app(PronosticoStockService::class)->estimar()['avisos']->first();
    expect($aviso['stock'])->toBe(10)->and($aviso['fecha'])->toBe('2026-11-16')
        ->and($aviso['estacional'])->toBe(0)->and($aviso['ingresos']->first()['cantidad'])->toBe(120)
        ->and($aviso['ingresos']->first()['intervalo'])->toBe(30.0)
        ->and(array_key_exists('reponer', $aviso))->toBeFalse();
});

test('stock agotado genera aviso urgente hoy sin inventar fecha pasada', function () {
    ingresoCalendario($this, '2026-09-01 09:00:00', 6);
    foreach (['2026-10-10', '2026-10-20', '2026-11-01'] as $fecha) {
        ventaCalendario($this, $fecha.' 12:00:00', 2);
    }
    $aviso = app(PronosticoStockService::class)->estimar()['avisos']->first();
    expect($aviso['fecha'])->toBe('2026-11-06')->and($aviso['urgencia'])->toContain('Urgente');
    expect(collect(calendarioPeriodo('2026-11-06')['dias'])->first(fn ($d) => $d['fecha']->isToday())['avisos'])->toHaveCount(1);
});

test('pronostico insuficiente no genera fechas sin ventas o con historial escaso', function () {
    ingresoCalendario($this, '2026-11-01 09:00:00', 20);
    ventaCalendario($this, '2026-11-02 12:00:00', 10);
    $resultado = app(PronosticoStockService::class)->estimar();
    expect($resultado['avisos'])->toBeEmpty()->and($resultado['insuficientes'])->toHaveCount(2)
        ->and(array_key_exists('fecha', $resultado['insuficientes']->first()))->toBeFalse();
    $this->actingAs($this->admin)->get('/reportes?seccion=calendario')->assertOk()->assertSee('Datos insuficientes para estimar');
});

test('pronostico aplica estacionalidad solo con dos anios equivalentes suficientes', function () {
    ingresoCalendario($this, '2023-01-01 09:00:00', 300);
    foreach ([2024, 2025] as $anio) {
        foreach (['10-10', '10-20', '11-01'] as $dia) {
            ventaCalendario($this, "$anio-$dia 12:00:00", 10);
        }
        foreach (['11-08', '11-15', '11-20'] as $dia) {
            ventaCalendario($this, "$anio-$dia 12:00:00", 30);
        }
    }
    foreach (['2026-10-10', '2026-10-20', '2026-11-01'] as $fecha) {
        ventaCalendario($this, $fecha.' 12:00:00', 10);
    }
    $aviso = app(PronosticoStockService::class)->estimar()['avisos']->first();
    // Stock 30; ritmo reciente 30/56; factor estacional (180/28)/(60/56) = 6.
    expect($aviso['stock'])->toBe(30)->and($aviso['estacional'])->toBe(2)->and($aviso['fecha'])->toBe('2026-11-16');
});

test('patron bisiesto solo compara ocurrencias reales y tablas largas se paginan', function () {
    ventaCalendario($this, '2024-02-29 12:00:00', 2);
    for ($i = 1; $i <= 26; $i++) {
        ventaCalendario($this, sprintf('2026-10-%02d 12:00:00', $i));
    }
    $this->actingAs($this->admin)->get('/reportes?seccion=fechas&patron=02-29&anio=2026&pagina_fechas=2')->assertOk()
        ->assertViewHas('analisis', fn ($a) => $a['ocurrencias']->count() === 1 && $a['exactas']->total() === 26 && $a['exactas']->count() === 6);
});

test('producto retirado conserva oferta informativa pero nunca aparece como vigente valida', function () {
    DB::table('producto')->where('codigo', 'A')->update(['deleted_at' => now()]);
    DB::table('promocion')->insert(['codigoProducto' => 'A', 'porcentajeDescuento' => 10, 'fechaInicio' => '2026-11-01 00:00:00', 'fechaFin' => '2026-11-30 23:59:59', 'activa' => true]);
    $dia = collect(calendarioPeriodo('2026-11-06')['dias'])->first(fn ($d) => $d['fecha']->isToday());
    expect($dia['ofertas']->first()->estado)->toBe('Producto retirado (no válida)');
});

test('productos y categorias ordenan unidades aunque el ingreso tenga otro orden', function () {
    $categoria = DB::table('categoria')->insertGetId(['nombre' => 'Otra'], 'idCategoria');
    DB::table('producto')->where('codigo', 'B')->update(['idCategoria' => $categoria]);
    ventaCalendario($this, '2026-11-01 12:00:00', 2, 10000);
    ventaCalendario($this, '2026-11-01 13:00:00', 10, 1, 'B');
    $rankings = app(AnalisisFechasService::class)->explorar(2026, null, '2026-11-01')['rankings'];
    expect($rankings['productos']->first()['codigo'])->toBe('B')
        ->and($rankings['categorias']->first()['nombre'])->toBe('Otra');
});

test('costos registrados en cero son validos y se distinguen de costos ausentes', function () {
    ingresoCalendario($this, '2026-11-01 23:59:59', 10, 0);
    ventaCalendario($this, '2026-11-01 12:00:00', 2, 1000);
    $producto = app(AnalisisFechasService::class)->explorar(2026, null, '2026-11-01')['rankings']['productos']->first();
    expect($producto['costo'])->toBe(0.0)->and($producto['margen'])->toBe(1000.0)->and($producto['sinCosto'])->toBe(0);
});

test('calendario no aumenta consultas por cada producto y no modifica movimientos', function () {
    DB::enableQueryLog();
    calendarioPeriodo('2026-11-06');
    $cantidad = count(DB::getQueryLog());
    DB::disableQueryLog();
    for ($n = 1; $n <= 30; $n++) {
        DB::table('producto')->insert(['codigo' => 'EXTRA-'.$n, 'nombre' => 'Extra '.$n]);
    }
    DB::enableQueryLog();
    DB::flushQueryLog();
    calendarioPeriodo('2026-11-06');
    expect(count(DB::getQueryLog()))->toBe($cantidad);
    expect(collect(DB::getQueryLog())->every(fn ($consulta) => str_starts_with(strtolower($consulta['query']), 'select')))->toBeTrue();
    DB::disableQueryLog();
    expect(DB::table('salida')->count())->toBe(0)->and(DB::table('ingreso')->count())->toBe(0);
});

test('sin ingresos o fuera del horizonte no publica fechas de agotamiento', function () {
    ingresoCalendario($this, '2026-09-01 09:00:00', 10000);
    foreach (['2026-10-10', '2026-10-20', '2026-11-01'] as $fecha) {
        ventaCalendario($this, $fecha.' 12:00:00', 2);
        ventaCalendario($this, $fecha.' 12:00:00', 2, 1000, 'B');
    }
    $resultado = app(PronosticoStockService::class)->estimar();
    expect($resultado['avisos'])->toBeEmpty()->and($resultado['insuficientes'])->toHaveCount(2)
        ->and($resultado['insuficientes']->first()['motivo'])->toContain('90 días');
});

test('rechaza filtros nuevos invalidos', function (array $filtros) {
    $this->actingAs($this->admin)->getJson('/reportes?'.http_build_query(['seccion' => 'calendario'] + $filtros))->assertUnprocessable();
})->with([
    [['vista' => 'dia']], [['fecha_calendario' => '2026-02-30']], [['dia' => '2026-11-31']],
    [['patron' => '02-30']], [['patron' => '13-01']], [['anio' => 0]], [['pagina_fechas' => -1]],
]);
