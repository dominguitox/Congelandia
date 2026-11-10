<div class="stats-grid">
    @foreach([
        ['Ventas del período', $moneda($datos['ingresos']), 'Incluye ventas al fiado'],
        ['Transacciones', $numero($datos['ventas']), 'Tickets de venta válidos'],
        ['Unidades vendidas', $numero($datos['unidades']), 'Excluye mermas y eliminados'],
        ['Ticket promedio', $moneda($datos['ticket']), 'Monto por transacción'],
    ] as [$label, $valor, $detalle])
        <div class="stat-tile"><div class="stat-tile-info"><span>{{ $label }}</span><strong>{{ $valor }}</strong><small>{{ $detalle }}</small></div></div>
    @endforeach
</div>

@if($datos['ventas'] === 0)
    <div class="panel"><div class="empty-state"><strong>No hay ventas en este período</strong><p>Prueba otras fechas. Los reportes se completarán con las ventas registradas en Congelandia.</p></div></div>
@else
    <div class="reportes-columnas">
        <section class="panel">
            <div class="panel-head"><div><h2>Evolución de ventas</h2><p>Monto vendido por día, incluyendo días sin ventas.</p></div></div>
            <div class="panel-body">
                <div class="reportes-chart-scroll" tabindex="0" aria-label="Gráfico de ventas diarias, desplazable">
                    <div class="reportes-barras" style="--barras: {{ $datos['dias']->count() }}">
                        @foreach($datos['dias'] as $dia)
                            <div class="reportes-barra-col" title="{{ $dia['fecha'] }}: {{ $moneda($dia['ingresos']) }}, {{ $dia['ventas'] }} ventas">
                                <div class="reportes-barra-pista"><div class="reportes-barra" style="--valor: {{ round(100 * $dia['ingresos'] / max(1, $datos['dias']->max('ingresos')), 2) }}%"></div></div>
                                <span>{{ \Carbon\Carbon::parse($dia['fecha'])->format('d/m') }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
                <details class="reportes-detalle"><summary>Ver cifras por día</summary>
                    <div class="data-table-wrap"><table class="data-table"><thead><tr><th>Fecha</th><th>Transacciones</th><th>Ventas</th></tr></thead><tbody>
                        @foreach($datos['dias'] as $dia)<tr><td>{{ \Carbon\Carbon::parse($dia['fecha'])->format('d/m/Y') }}</td><td>{{ $numero($dia['ventas']) }}</td><td>{{ $moneda($dia['ingresos']) }}</td></tr>@endforeach
                    </tbody></table></div>
                </details>
            </div>
        </section>
        <section class="panel">
            <div class="panel-head"><div><h2>Ventas por categoría</h2><p>Participación en el monto vendido.</p></div></div>
            <div class="panel-body reportes-ranking">
                @foreach($datos['categorias'] as $categoria)
                    <div><div class="reportes-ranking-label"><strong>{{ $categoria->nombre }}</strong><span>{{ $moneda($categoria->ingresos) }}</span></div>
                        <div class="reportes-progreso" role="img" aria-label="{{ $categoria->nombre }}: {{ round(100 * $categoria->ingresos / max(1, $datos['ingresos'])) }}% de las ventas"><span style="width: {{ 100 * $categoria->ingresos / max(1, $datos['ingresos']) }}%"></span></div>
                        <small>{{ $numero($categoria->unidades) }} unidades · {{ round(100 * $categoria->ingresos / max(1, $datos['ingresos'])) }}%</small>
                    </div>
                @endforeach
            </div>
        </section>
    </div>
    <section class="panel">
        <div class="panel-head"><div><h2>Productos más vendidos</h2><p>Ordenados por unidades vendidas. Se conserva el precio cobrado, incluidas las ofertas.</p></div><span class="pill pill--primary">{{ $datos['productos']->count() }} productos</span></div>
        <div class="data-table-wrap"><table class="data-table"><thead><tr><th>Producto</th><th>Unidades</th><th>Ventas</th></tr></thead><tbody>
            @foreach($datos['productos'] as $producto)
                <tr><td><span class="celda-principal">{{ $producto->nombre }}</span><span class="celda-sub">{{ $producto->codigo }}</span></td><td>{{ $numero($producto->unidades) }}</td><td>{{ $moneda($producto->ingresos) }}</td></tr>
            @endforeach
        </tbody></table></div>
    </section>
@endif
<p class="reportes-nota">Ventas registradas, no dinero recaudado: el fiado se incluye aunque siga pendiente de pago.</p>
