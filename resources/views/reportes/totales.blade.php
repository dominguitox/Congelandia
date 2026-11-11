<div class="stats-grid" aria-label="Totales del rango normal de reportes">
    @foreach([
        ['Ingresos del rango', $moneda($datos['ingresos'])],
        ['Tickets del rango', $numero($datos['ventas'])],
        ['Unidades del rango', $numero($datos['unidades'])],
        ['Ticket promedio', $moneda($datos['ticket'])],
    ] as [$label, $valor])
        <div class="stat-tile"><div class="stat-tile-info"><span>{{ $label }}</span><strong>{{ $valor }}</strong></div></div>
    @endforeach
</div>
<p class="reportes-nota">Totales del {{ \Carbon\CarbonImmutable::parse($fechaInicio)->format('d/m/Y') }} al {{ \Carbon\CarbonImmutable::parse($fechaFin)->format('d/m/Y') }}. Ventas registradas, incluido fiado; no equivale a recaudación. La selección de un día o patrón se analiza por separado hasta aplicar su fecha al filtro.</p>
