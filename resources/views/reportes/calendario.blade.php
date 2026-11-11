@include('reportes.totales')
<section class="panel">
    <div class="panel-head"><div><h2>Calendario</h2><p>{{ $calendario['inicio']->locale(config('app.locale'))->translatedFormat('d F Y') }} al {{ $calendario['fin']->locale(config('app.locale'))->translatedFormat('d F Y') }} · {{ config('app.timezone') }}</p></div></div>
    <div class="panel-body">
        <nav class="reportes-cal-controles" aria-label="Navegación del calendario">
            @php
                $anterior = $vista === 'mes' ? $fechaCalendario->subMonthNoOverflow() : $fechaCalendario->subWeek();
                $siguiente = $vista === 'mes' ? $fechaCalendario->addMonthNoOverflow() : $fechaCalendario->addWeek();
            @endphp
            @if($anterior->year >= 1900)<a class="btn-soft" rel="prev" href="{{ $urlReporte(['fecha_calendario' => $anterior->toDateString()]) }}">Período anterior</a>@endif
            <a class="btn-soft" href="{{ $urlReporte(['fecha_calendario' => now()->toDateString()]) }}">Hoy</a>
            @if($siguiente->year <= 2100)<a class="btn-soft" rel="next" href="{{ $urlReporte(['fecha_calendario' => $siguiente->toDateString()]) }}">Período siguiente</a>@endif
            @foreach(['mes' => 'Mes', 'semana' => 'Semana'] as $valor => $etiqueta)
                <a class="{{ $vista === $valor ? 'btn-solid' : 'btn-outline' }}" @if($vista === $valor) aria-current="true" @endif href="{{ $urlReporte(['vista' => $valor]) }}">{{ $etiqueta }}</a>
            @endforeach
        </nav>
        <form class="reportes-cal-controles" method="GET" action="{{ route('reportes.index') }}">
            @foreach($contexto as $clave => $valor) @if($clave !== 'fecha_calendario' && $valor !== null)<input type="hidden" name="{{ $clave }}" value="{{ $valor }}">@endif @endforeach
            <label for="ir-calendario">Ir a fecha</label><input class="form-control" type="date" id="ir-calendario" name="fecha_calendario" value="{{ $fechaCalendario->toDateString() }}" min="1900-01-01" max="2100-12-31" required><button class="btn-outline">Mostrar período</button>
        </form>
        <p class="reportes-nota">Selecciona un día para ver sus ventas. Despliega un evento para consultar sus datos. Oferta vencida: terminó por fecha; pausada: desactivada manualmente, no válida. Feriados nacionales disponibles: 2025–2027.</p>
        @if($calendario['sinCobertura'])<p class="alert alert-warning" role="status">Sin catálogo de feriados para {{ implode(', ', $calendario['sinCobertura']) }}. No significa que esos años carezcan de feriados; consulta cómo ampliar la cobertura en la documentación de Reportes.</p>@endif
        <div class="reportes-cal-scroll" tabindex="0" aria-label="Calendario desplazable, de lunes a domingo">
            <table class="reportes-calendario">
                <caption class="visually-hidden">{{ $vista === 'mes' ? 'Vista mensual con días adyacentes' : 'Vista semanal' }}. Ofertas, feriados y estimaciones de agotamiento.</caption>
                <thead><tr>@foreach(['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado', 'Domingo'] as $nombre)<th scope="col">{{ $nombre }}</th>@endforeach</tr></thead>
                <tbody>@foreach(array_chunk($calendario['dias'], 7) as $semana)<tr>
                    @foreach($semana as $celda)
                        @php($fecha = $celda['fecha']->toDateString())
                        <td @class(['reportes-cal-adyacente' => $celda['adyacente'], 'reportes-cal-seleccion' => $dia === $fecha])>
                            <a class="reportes-cal-dia" href="{{ $urlDia($fecha) }}" aria-label="Consultar ventas del {{ $celda['fecha']->format('d/m/Y') }}" @if($celda['fecha']->isToday()) aria-current="date" @endif>
                                <time datetime="{{ $fecha }}">{{ $celda['fecha']->format('d/m') }}</time>{{ $celda['fecha']->isToday() ? ' · Hoy' : '' }}{{ $dia === $fecha ? ' · Seleccionado' : '' }}
                            </a>
                            <small class="reportes-cal-resumen">{{ $moneda($celda['ventas']?->ingresos ?? 0) }} · {{ $numero($celda['ventas']?->ventas ?? 0) }} salidas</small>
                            <div class="reportes-cal-eventos">
                                @foreach($celda['feriados'] as $feriado)
                                    <details class="reportes-evento reportes-evento--feriado"><summary>Feriado · {{ $feriado['nombre'] }}</summary><p>Nacional · {{ $celda['fecha']->format('d/m/Y') }}</p><a href="{{ $urlDia($fecha) }}">Consultar ventas de esta fecha</a></details>
                                @endforeach
                                @foreach($celda['ofertas'] as $oferta)
                                    <details data-oferta-id="{{ $oferta->idPromocion }}" style="--oferta-tono: {{ $oferta->tono }}" @class(['reportes-evento', 'reportes-evento--oferta', 'reportes-evento--pausado' => ! $oferta->activa || $oferta->productoEliminado !== null])>
                                        <summary>Oferta #{{ $oferta->idPromocion }} · {{ $oferta->nombre }} · {{ $numero($oferta->porcentajeDescuento) }}% · {{ $oferta->estado }}<span class="reportes-oferta-tramo">{{ $oferta->tramo }}</span></summary>
                                        <p>{{ $oferta->codigoProducto }} · Descuento {{ number_format($oferta->porcentajeDescuento, 2, ',', '.') }}%<br>{{ \Carbon\CarbonImmutable::parse($oferta->fechaInicio)->format('d/m/Y H:i') }} al {{ \Carbon\CarbonImmutable::parse($oferta->fechaFin)->format('d/m/Y H:i') }}</p>
                                        <a href="{{ $urlDia($fecha) }}">Consultar ventas de esta fecha</a>
                                    </details>
                                @endforeach
                                @foreach($celda['avisos'] as $aviso)
                                    <details class="reportes-evento reportes-evento--stock"><summary>Agotamiento estimado · {{ $aviso['nombre'] }} · {{ $aviso['urgencia'] }}</summary><p>Fecha estimada: {{ \Carbon\CarbonImmutable::parse($aviso['estimada'])->format('d/m/Y') }}. Revisar existencias: {{ $numero($aviso['stock']) }} unidades registradas.</p><a href="#pronostico-stock">Ver criterio e ingresos recibidos</a></details>
                                @endforeach
                            </div>
                        </td>
                    @endforeach
                </tr>@endforeach</tbody>
            </table>
        </div>
    </div>
</section>
@include('reportes.seleccion-fecha')
@include('reportes.pronostico')
@include('reportes.explorar-fechas')
