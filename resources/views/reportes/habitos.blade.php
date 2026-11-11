<div class="stats-grid">
    @foreach([
        ['Hora de mayor actividad', $datos['horaPunta'] === null ? 'Sin datos' : sprintf('%02d–%02d h', $datos['horaPunta'], $datos['horaPunta'] + 1), 'Según salidas del período'],
        ['Día más fuerte', $datos['diaFuerte']['nombre'] ?? 'Sin datos', $datos['diaFuerte'] ? $moneda($datos['diaFuerte']['promedio']).' de venta media diaria' : 'Registra ventas para comparar'],
        ['Día más tranquilo', $datos['diaDebil']['nombre'] ?? 'Sin datos', $datos['diaDebil'] ? $moneda($datos['diaDebil']['promedio']).' de venta media diaria' : 'Registra ventas para comparar'],
        ['Salidas analizadas', $numero($datos['ventas']), 'Cada salida se cuenta una sola vez'],
    ] as [$label, $valor, $detalle])
        <div class="stat-tile"><div class="stat-tile-info"><span>{{ $label }}</span><strong>{{ $valor }}</strong><small>{{ $detalle }}</small></div></div>
    @endforeach
</div>
<section class="panel">
    <div class="panel-head"><div><h2>Actividad por día y hora</h2><p>Total de salidas del período. Mayor intensidad significa más salidas.</p></div><span class="pill pill--primary">{{ $numero($datos['ventas']) }} salidas</span></div>
    <div class="panel-body">
        @if($datos['ventas'] === 0)<p class="reportes-contexto">No hay ventas en este período. Selecciona otras fechas para explorar los hábitos de compra.</p>@endif
        <div class="reportes-mapa-scroll" tabindex="0" aria-label="Mapa de actividad de las 24 horas, desplazable">
            <table class="reportes-mapa"><caption class="visually-hidden">Salidas por día de semana y hora; totales del período seleccionado.</caption>
                <thead><tr><th scope="col">Día / hora</th>@for($hora = 0; $hora < 24; $hora++)<th scope="col">{{ sprintf('%02d', $hora) }}</th>@endfor</tr></thead>
                <tbody>@foreach($datos['semana'] as $indice => $dia)
                    <tr><th scope="row">{{ $dia['nombre'] }}</th>@foreach($datos['mapa'][$indice] as $hora => $cantidad)
                        @php($nivel = $cantidad === 0 ? 0 : max(1, (int) ceil(4 * $cantidad / max(1, $datos['maximo']))))
                        <td class="reportes-calor reportes-calor--{{ $nivel }}" title="{{ $dia['nombre'] }} {{ sprintf('%02d:00', $hora) }}: {{ $cantidad }} salidas"><span>{{ $cantidad }}</span></td>
                    @endforeach</tr>
                @endforeach</tbody>
            </table>
        </div>
        <div class="reportes-leyenda"><span>Menos actividad</span>@for($nivel = 0; $nivel <= 4; $nivel++)<i class="reportes-calor--{{ $nivel }}" aria-hidden="true"></i>@endfor<span>Más actividad</span></div>
        <p class="reportes-nota">Las horas corresponden a la fecha registrada por la app. Se muestran las 24 horas para no ocultar ventas fuera del horario habitual.</p>
    </div>
</section>
<section class="panel">
    <div class="panel-head"><div><h2>Comparación por día de la semana</h2><p>La venta media se divide por las veces que aparece cada día en el período, incluidos los días sin ventas.</p></div></div>
    <div class="data-table-wrap"><table class="data-table"><thead><tr><th>Día</th><th>Días en el período</th><th>Salidas</th><th>Ventas totales</th><th>Venta media diaria</th></tr></thead><tbody>
        @foreach($datos['semana'] as $dia)<tr><td>{{ $dia['nombre'] }}</td><td>{{ $dia['ocurrencias'] }}</td><td>{{ $numero($dia['ventas']) }}</td><td>{{ $moneda($dia['ingresos']) }}</td><td>{{ $dia['promedio'] === null ? 'Fuera del período' : $moneda($dia['promedio']) }}</td></tr>@endforeach
    </tbody></table></div>
</section>
