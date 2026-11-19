<div class="stats-grid" aria-label="Totales del rango normal de reportes">
    @foreach([
        ['Ingresos del rango', $moneda($datos['ingresos'])],
        ['Salidas del rango', $numero($datos['ventas'])],
        ['Unidades del rango', $numero($datos['unidades'])],
        ['Ingreso promedio por salida', $moneda($datos['ingresoPromedioPorSalida'])],
    ] as [$label, $valor])
        <div class="stat-tile"><div class="stat-tile-info"><span>{{ $label }}</span><strong>{{ $valor }}</strong></div></div>
    @endforeach
</div>
