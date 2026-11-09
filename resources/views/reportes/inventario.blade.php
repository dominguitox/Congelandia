<div class="stats-grid">
    @foreach([
        ['Productos bajo stock', $datos['bajoStock']->count(), $datos['criticos'].' con 3 unidades o menos'],
        ['Lotes para revisar', $datos['lotes']->count(), 'Vencidos o por vencer en 15 días'],
        ['Valor en riesgo estimado', $moneda($datos['valorRiesgo']), 'A precio de venta, sin descuentos'],
        ['Reposición sugerida', $numero($datos['reposicion']).' uds.', 'Para llegar a 20 por producto'],
    ] as [$label, $valor, $detalle])
        <div class="stat-tile"><div class="stat-tile-info"><span>{{ $label }}</span><strong>{{ $valor }}</strong><small>{{ $detalle }}</small></div></div>
    @endforeach
</div>
<div class="reportes-columnas">
    <section class="panel">
        <div class="panel-head"><div><h2>Prioridad de reposición</h2><p>Umbral provisional: menos de 10 unidades. Objetivo: 20.</p></div></div>
        <div class="data-table-wrap"><table class="data-table"><thead><tr><th>Producto</th><th>Stock actual</th><th>Reponer</th></tr></thead><tbody>
            @forelse($datos['bajoStock'] as $producto)
                <tr><td><span class="celda-principal">{{ $producto->nombre }}</span><span class="celda-sub">{{ $producto->categoria }} · {{ $producto->codigo }}</span></td>
                    <td><span class="pill pill--{{ $producto->stock <= 3 ? 'danger' : 'warning' }}">{{ $producto->stock < 0 ? 'Revisar: ' : '' }}{{ $numero($producto->stock) }} uds.</span></td><td>{{ $numero($producto->reponer) }} uds.</td></tr>
            @empty
                <tr><td colspan="3"><div class="empty-state"><strong>Sin alertas de reposición</strong><p>No hay productos activos por debajo del umbral.</p></div></td></tr>
            @endforelse
        </tbody></table></div>
    </section>
    <section class="panel">
        <div class="panel-head"><div><h2>Riesgo por vencimiento</h2><p>Valor estimado según fecha de vencimiento.</p></div></div>
        <div class="panel-body reportes-ranking">
            @foreach($datos['ventanas'] as $ventana)
                <div><div class="reportes-ranking-label"><strong>{{ $ventana['nombre'] }}</strong><span>{{ $moneda($ventana['valor']) }}</span></div>
                    <div class="reportes-progreso reportes-progreso--{{ $loop->index < 2 ? 'danger' : 'warning' }}"><span style="width: {{ 100 * $ventana['valor'] / max(1, $datos['ventanas']->max('valor')) }}%"></span></div>
                    <small>{{ $numero($ventana['cantidad']) }} unidades estimadas</small>
                </div>
            @endforeach
            @if($datos['sinPrecio'])<p class="reportes-nota">{{ $datos['sinPrecio'] }} lotes sin precio vigente: sus unidades se muestran, pero no se incluyen en el valor.</p>@endif
        </div>
    </section>
</div>
<section class="panel">
    <div class="panel-head"><div><h2>Vencimientos para revisar</h2><p>Primero los vencidos y luego los más cercanos.</p></div><span class="pill pill--warning">Cantidades estimadas</span></div>
    <div class="panel-body"><p class="reportes-nota">La app no vincula salidas con lotes. Esta estimación descuenta las salidas del producto empezando por el lote que vence primero (FEFO). Confirma las cantidades físicas antes de tomar una decisión. Los productos vencidos requieren revisión y retiro.</p></div>
    <div class="data-table-wrap"><table class="data-table"><thead><tr><th>Producto</th><th>Vencimiento</th><th>Prioridad</th><th>Unidades estimadas</th><th>Valor estimado</th></tr></thead><tbody>
        @forelse($datos['lotes'] as $lote)
            <tr><td><span class="celda-principal">{{ $lote->nombre }}</span><span class="celda-sub">{{ $lote->codigo }}</span></td><td>{{ \Carbon\Carbon::parse($lote->vencimiento)->format('d/m/Y') }}</td>
                <td><span class="pill pill--{{ $lote->dias <= 3 ? 'danger' : 'warning' }}">{{ $lote->dias < 0 ? 'Vencido' : ($lote->dias === 0 ? 'Vence hoy' : 'En '.$lote->dias.' días') }}</span></td><td>{{ $numero($lote->cantidad) }}</td><td>{{ $lote->valor === null ? 'Sin precio' : $moneda($lote->valor) }}</td></tr>
        @empty
            <tr><td colspan="5"><div class="empty-state"><strong>Sin vencimientos próximos estimados</strong><p>No se estiman unidades vencidas ni por vencer dentro de 15 días.</p></div></td></tr>
        @endforelse
    </tbody></table></div>
</section>
