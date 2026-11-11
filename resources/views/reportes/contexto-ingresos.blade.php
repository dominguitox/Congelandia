@forelse($contextos as $contextoIngreso)
    <details><summary>{{ $contextoIngreso['proveedor'] }} · {{ $contextoIngreso['recepciones'] }} fechas de recepción · {{ $numero($contextoIngreso['cantidad']) }} unidades en 365 días</summary>
        <p>{{ $contextoIngreso['intervalo'] === null ? 'Una sola fecha: sin frecuencia estimable.' : 'Intervalo medio entre recepciones: '.number_format($contextoIngreso['intervalo'], 1, ',', '.').' días.' }}</p>
        <ul>@foreach($contextoIngreso['ultimos'] as $ingreso)<li>{{ \Carbon\CarbonImmutable::parse($ingreso->fecha)->format('d/m/Y') }} · {{ $numero($ingreso->cantidad) }} unidades recibidas</li>@endforeach</ul>
        <small>Recepciones ya registradas; no son pedidos abiertos ni tiempos de entrega.</small>
    </details>
@empty
    Sin ingresos recibidos en los últimos 365 días.
@endforelse
