<section class="panel" id="detalle-fecha" aria-labelledby="titulo-fecha">
    <div class="panel-head"><div><h2 id="titulo-fecha">{{ $patron ? 'Patrón '.substr($patron, 3, 2).'/'.substr($patron, 0, 2) : ($dia ? 'Ventas del '.\Carbon\CarbonImmutable::parse($dia)->format('d/m/Y') : 'Selecciona una fecha o patrón') }}</h2></div>
        @if($dia || $patron)<a class="btn-soft" href="{{ $urlReporte(['dia' => null, 'patron' => null]) }}#detalle-fecha">Limpiar selección y volver al rango</a>@endif
    </div>
    <div class="panel-body">
        @if($dia && ! $patron)<a class="btn-solid" href="{{ route('reportes.index', ['seccion' => 'ventas', 'fecha_inicio' => $dia, 'fecha_fin' => $dia]) }}">Aplicar esta fecha al filtro de reportes</a>
            <a class="btn-outline" href="{{ $urlReporte(['seccion' => 'calendario', 'fecha_calendario' => $dia]) }}#detalle-fecha">Ver fecha en calendario</a>
        @endif
        @if($dia || $patron)
            <p class="reportes-nota">{{ $analisis['ocurrencias']->count() }} ocurrencias anuales con ventas · Ingresos {{ $moneda($analisis['ocurrencias']->sum('ingresos')) }} · {{ $numero($analisis['ocurrencias']->sum('unidades')) }} unidades · {{ $numero($analisis['ocurrencias']->sum('ventas')) }} salidas.</p>
            @if($patron)
                <div class="data-table-wrap reportes-tabla-larga" tabindex="0" aria-label="Desglose anual desplazable"><table class="data-table"><thead><tr><th scope="col">Ocurrencia anual</th><th scope="col">Ingresos</th><th scope="col">Unidades</th><th scope="col">Salidas</th></tr></thead><tbody>
                    @forelse($analisis['ocurrencias'] as $fila)<tr><td><a href="{{ $urlDia($fila->fecha) }}">{{ \Carbon\CarbonImmutable::parse($fila->fecha)->format('d/m/Y') }} · Ver detalle</a></td><td>{{ $moneda($fila->ingresos) }}</td><td>{{ $numero($fila->unidades) }}</td><td>{{ $numero($fila->ventas) }}</td></tr>@empty<tr><td colspan="4">Sin ocurrencias con ventas.</td></tr>@endforelse
                </tbody></table></div>
            @endif
            <p class="reportes-nota">Precio realmente cobrado, ponderado por unidades. Costo y margen unitarios <strong>estimados</strong>: compras ponderadas hasta cada ocurrencia, incluidos ingresos de ese mismo día. No existe trazabilidad entre venta y lote. Con costos faltantes no se calcula un margen completo. Las categorías usan la clasificación registrada actualmente.</p>
            @foreach(['productos' => 'Productos', 'categorias' => 'Categorías'] as $tipo => $nombre)
                <details class="reportes-detalle" open><summary>{{ $nombre }} más {{ $tipo === 'categorias' ? 'vendidas' : 'vendidos' }} · {{ $analisis['rankings'][$tipo]->count() }} resultados · orden por unidades</summary>
                    <div class="data-table-wrap reportes-tabla-larga" tabindex="0" aria-label="Ranking de {{ strtolower($nombre) }}, desplazable"><table class="data-table"><thead><tr><th scope="col">{{ $nombre }}</th><th scope="col">Unidades</th><th scope="col">Ingresos</th><th scope="col">Precio unitario promedio</th><th scope="col">Costo unitario estimado</th><th scope="col">Margen unitario estimado</th><th scope="col">Ocurrencias</th></tr></thead><tbody>
                        @forelse($analisis['rankings'][$tipo] as $fila)<tr><td>{{ $fila['nombre'] }}@if(isset($fila['codigo']))<span class="celda-sub">{{ $fila['codigo'] }}</span>@endif</td><td>{{ $numero($fila['unidades']) }}</td><td>{{ $moneda($fila['ingresos']) }}</td><td>{{ $fila['precio'] === null ? 'Sin unidades' : '$'.number_format($fila['precio'], 2, ',', '.') }}</td><td>{{ $fila['costo'] === null ? 'Sin costo histórico completo' : '$'.number_format($fila['costo'], 2, ',', '.') }}@if($fila['sinCosto'])<small class="celda-sub">{{ $numero($fila['sinCosto']) }} unidades sin costo histórico</small>@endif</td><td>{{ $fila['margen'] === null ? 'No estimable' : '$'.number_format($fila['margen'], 2, ',', '.') }}</td><td>{{ $numero($fila['ocurrencias']) }}</td></tr>@empty<tr><td colspan="7">Sin ventas para esta selección.</td></tr>@endforelse
                    </tbody></table></div>
                </details>
            @endforeach
            @if($detalleDia)
                <details class="reportes-detalle" open><summary>Detalle de ventas de la fecha · {{ $detalleDia->total() }} líneas</summary><div class="data-table-wrap"><table class="data-table"><thead><tr><th scope="col">Salida</th><th scope="col">Hora</th><th scope="col">Producto</th><th scope="col">Unidades</th><th scope="col">Precio cobrado</th><th scope="col">Ingresos</th></tr></thead><tbody>
                    @forelse($detalleDia as $linea)<tr><td>#{{ $linea->idSalida }}</td><td>{{ \Carbon\CarbonImmutable::parse($linea->fecha)->format('H:i') }}</td><td>{{ $linea->nombre }}</td><td>{{ $numero($linea->cantidad) }}</td><td>{{ $moneda($linea->precioCobrado) }}</td><td>{{ $moneda($linea->ingresos) }}</td></tr>@empty<tr><td colspan="6">No hay ventas registradas en esta fecha.</td></tr>@endforelse
                </tbody></table></div>{{ $detalleDia->links('reportes.paginacion', ['etiqueta' => 'líneas de venta']) }}</details>
            @endif
        @else
            <p>Selecciona un día del calendario, una fecha exacta o un patrón recurrente para consultar ventas y rankings.</p>
        @endif
    </div>
</section>
