<section class="panel">
    <div class="panel-head"><div><h2>Fechas de mayor venta</h2><p>Ordenadas por ingresos; se excluyen mermas y movimientos eliminados. Las salidas se cuentan una vez.</p></div></div>
    <div class="panel-body">
        <form method="GET" action="{{ route('reportes.index') }}" class="reportes-cal-controles">
            @foreach($contexto as $clave => $valor) @if($clave !== 'anio' && $valor !== null)<input type="hidden" name="{{ $clave }}" value="{{ $valor }}">@endif @endforeach
            <label for="anio-ventas">Año de fechas exactas</label><input class="form-control" type="number" name="anio" id="anio-ventas" min="1900" max="2100" value="{{ $anio }}" required><button class="btn-outline">Consultar año</button>
        </form>
        <p class="reportes-nota">Años con ventas registradas: {{ $analisis['anios']->implode(', ') ?: 'Sin historial' }}.</p>
        <div class="reportes-columnas">
            <div><h3>Fechas exactas · {{ $anio }}</h3><div class="data-table-wrap"><table class="data-table"><thead><tr><th scope="col">Fecha</th><th scope="col">Ingresos</th><th scope="col">Unidades</th><th scope="col">Salidas</th></tr></thead><tbody>
                @forelse($analisis['exactas'] as $fila)<tr><td><a href="{{ $urlDia($fila->fecha) }}">{{ \Carbon\CarbonImmutable::parse($fila->fecha)->format('d/m/Y') }}</a></td><td>{{ $moneda($fila->ingresos) }}</td><td>{{ $numero($fila->unidades) }}</td><td>{{ $numero($fila->ventas) }}</td></tr>@empty<tr><td colspan="4">Sin ventas en este año.</td></tr>@endforelse
            </tbody></table></div>{{ $analisis['exactas']->links('reportes.paginacion', ['etiqueta' => 'fechas exactas']) }}</div>
            <div><h3>Patrones de día/mes · Todo el historial</h3><div class="data-table-wrap"><table class="data-table"><thead><tr><th scope="col">Día/mes</th><th scope="col">Ingresos</th><th scope="col">Unidades</th><th scope="col">Salidas</th><th scope="col">Ocurrencias</th></tr></thead><tbody>
                @forelse($analisis['recurrentes'] as $fila)<tr><td><a href="{{ $urlReporte(['patron' => $fila->fecha, 'dia' => null]) }}#detalle-fecha">{{ substr($fila->fecha, 3, 2) }}/{{ substr($fila->fecha, 0, 2) }}</a></td><td>{{ $moneda($fila->ingresos) }}</td><td>{{ $numero($fila->unidades) }}</td><td>{{ $numero($fila->ventas) }}</td><td>{{ $numero($fila->ocurrencias) }}</td></tr>@empty<tr><td colspan="5">Sin historial de ventas.</td></tr>@endforelse
            </tbody></table></div>{{ $analisis['recurrentes']->links('reportes.paginacion', ['etiqueta' => 'patrones recurrentes']) }}</div>
        </div>
    </div>
</section>
