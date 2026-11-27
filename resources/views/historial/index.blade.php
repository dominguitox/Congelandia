@extends('layouts.app')

@section('title', 'Historial de Movimientos')

@push('css')
    @vite(['resources/css/historial.css'])
@endpush

@section('content')

<div class="historial-container">

    {{-- =====================================================
         CABECERA
    ====================================================== --}}

    <div class="historial-header">

        <div>
            <h2>Historial de Movimientos</h2>

            <p>
                Revisa los ingresos y salidas de productos
            </p>
        </div>

        <form class="historial-filtros" method="GET" action="{{ route('historial.index') }}">

            <div class="historial-search">

                <label for="buscarMovimiento">Buscar</label>

                <input
                    type="text"
                    id="buscarMovimiento"
                    name="buscar"
                    value="{{ $buscar }}"
                    placeholder="Buscar movimiento..."
                    autocomplete="off"
                >

            </div>

            <label for="filtroTipo">Tipo</label>
            <select id="filtroTipo" name="tipo" class="historial-select">

                <option value="todos">
                    Todos
                </option>

                <option value="ingreso" @selected($tipo === 'ingreso')>
                    Ingresos
                </option>

                @foreach($tiposSalida as $tipo)

                    <option value="salida:{{ mb_strtolower($tipo) }}" @selected(request('tipo') === 'salida:'.mb_strtolower($tipo))>
                        {{ $tipo }}
                    </option>

                @endforeach

            </select>
            <button type="submit" class="btn-outline">Filtrar</button>
        </form>

    </div>


    {{-- =====================================================
         ESTADÍSTICAS
    ====================================================== --}}

    <div class="historial-stats">

        <div class="stat-card">

            <div class="stat-icon stat-icon-total">
                ↕
            </div>

            <div>
                <span>Total movimientos</span>
                <strong>{{ $totalMovimientos }}</strong>
            </div>

        </div>


        <div class="stat-card">

            <div class="stat-icon stat-icon-ingreso">
                ↑
            </div>

            <div>
                <span>Ingresos</span>
                <strong>{{ $totalIngresos }}</strong>
            </div>

        </div>


        <div class="stat-card">

            <div class="stat-icon stat-icon-salida">
                ↓
            </div>

            <div>
                <span>Salidas</span>
                <strong>{{ $totalSalidas }}</strong>
            </div>

        </div>

    </div>


    {{-- =====================================================
         TABLA
    ====================================================== --}}

    @if($hayHistorico)
        <p>Los movimientos históricos usan el precio y costo disponibles del producto. Sus importes son de referencia.</p>
    @endif

    <div class="historial-card">

        <table class="historial-table">

            <thead>

                <tr>

                    <th>Movimiento</th>

                    <th>Fecha y Hora</th>

                    <th>Tipo</th>

                    <th>Artículos</th>

                    <th>Responsable</th>

                    <th>Total</th>

                    <th></th>

                </tr>

            </thead>


            <tbody id="movimientosBody">

                @forelse($movimientos as $movimiento)

                    @php
                        $fecha = \Carbon\Carbon::parse($movimiento['fecha']);
                    @endphp

                    <tr
                        class="movimiento-row"
                        data-tipo="{{ $movimiento['tipo_clase'] }}"
                        data-nombre-tipo="{{ strtolower($movimiento['tipo']) }}"
                        data-search="
                            {{ strtolower(
                                $movimiento['referencia'] . ' ' .
                                $movimiento['tipo'] . ' ' .
                                $movimiento['usuario'] . ' ' .
                                ($movimiento['proveedor'] ?? '')
                            ) }}
                        "
                    >

                        <td class="movimiento-id">

                            <strong>
                                {{ $movimiento['referencia'] }}
                            </strong>

                        </td>


                        <td>

                            {{ $fecha->format('d M Y, H:i') }}

                        </td>


                        <td>

                            @if($movimiento['tipo_clase'] === 'ingreso')

                                <span class="tipo-badge tipo-ingreso">
                                    ↑ {{ $movimiento['tipo'] }}
                                </span>

                            @else

                                <span class="tipo-badge tipo-salida">
                                    ↓ {{ $movimiento['tipo'] }}
                                </span>

                            @endif

                        </td>


                        <td>

                            <span class="cantidad-badge">

                                {{ $movimiento['cantidad'] }}

                                {{ $movimiento['cantidad'] == 1 ? 'artículo' : 'artículos' }}

                            </span>

                        </td>


                        <td>

                            {{ $movimiento['usuario'] }}

                        </td>


                        <td class="total">

                            @if($movimiento['tipo_clase'] === 'ingreso')

                                ${{ number_format($movimiento['total'], 0, ',', '.') }}

                            @elseif($movimiento['total'] > 0)

                                ${{ number_format($movimiento['total'], 0, ',', '.') }}

                            @else

                                —

                            @endif

                        </td>


                        <td class="acciones">

                            <button
                                type="button"
                                class="btn-expand"
                                data-target="detalle-{{ $movimiento['tipo_clase'] }}-{{ $movimiento['id'] }}"
                                aria-expanded="false"
                                title="Ver detalles"
                            >
                                ▼
                            </button>

                        </td>

                    </tr>


                    {{-- =================================================
                         DETALLE
                    ================================================== --}}

                    <tr
                        id="detalle-{{ $movimiento['tipo_clase'] }}-{{ $movimiento['id'] }}"
                        class="detalle-row"
                    >

                        <td colspan="7">

                            <div class="detalle-container">

                                <div class="detalle-header">

                                    <div>

                                        <h4>

                                            @if($movimiento['tipo_clase'] === 'ingreso')

                                                Detalle del Ingreso

                                            @else

                                                Detalle de la Salida

                                            @endif

                                        </h4>

                                        <span>
                                            {{ $movimiento['referencia'] }}
                                        </span>

                                    </div>


                                    @if($movimiento['tipo_clase'] === 'ingreso')

                                        <div class="detalle-extra">

                                            Proveedor:
                                            <strong>
                                                {{ $movimiento['proveedor'] }}
                                            </strong>

                                        </div>

                                    @endif

                                </div>


                                @forelse($movimiento['detalles'] as $item)

                                    <div class="detalle-item">

                                        <span class="detalle-cantidad">

                                            {{ $item['cantidad'] }}x

                                        </span>


                                        <div class="detalle-producto">

                                            <strong>
                                                {{ $item['producto'] }}
                                            </strong>

                                            <small>
                                                Código: {{ $item['codigo'] }}
                                            </small>

                                        </div>


                                        <div class="detalle-precio">

                                            @if($movimiento['tipo_clase'] === 'ingreso')

                                                ${{ number_format($item['precio'], 0, ',', '.') }}
                                                c/u

                                            @elseif($item['precio'] > 0)

                                                ${{ number_format($item['precio'], 0, ',', '.') }}
                                                c/u

                                            @else

                                                —

                                            @endif

                                        </div>


                                        <div class="detalle-subtotal">

                                            @if($item['precio'] > 0)

                                                ${{ number_format(
                                                    $item['cantidad'] * $item['precio'],
                                                    0,
                                                    ',',
                                                    '.'
                                                ) }}

                                            @else

                                                —

                                            @endif

                                        </div>

                                    </div>

                                @empty

                                    <div class="detalle-vacio">

                                        No hay productos registrados para este movimiento.

                                    </div>

                                @endforelse


                            </div>

                        </td>

                    </tr>

                @empty

                    <tr>

                        <td
                            colspan="7"
                            class="empty-message"
                        >

                            No existen movimientos registrados.

                        </td>

                    </tr>

                @endforelse


                <tr
                    id="sinResultados"
                    class="sin-resultados"
                    style="display:none;"
                >

                    <td colspan="7">

                        No se encontraron movimientos que coincidan con la búsqueda.

                    </td>

                </tr>

            </tbody>

        </table>

    </div>

    <nav aria-label="Páginas del historial">
        <p>{{ $movimientos->total() }} resultados · Página {{ $movimientos->currentPage() }} de {{ $movimientos->lastPage() }}</p>
        @if($movimientos->previousPageUrl())<a class="btn-outline" href="{{ $movimientos->previousPageUrl() }}" rel="prev">Anterior</a>@endif
        @if($movimientos->nextPageUrl())<a class="btn-outline" href="{{ $movimientos->nextPageUrl() }}" rel="next">Siguiente</a>@endif
    </nav>
</div>

@endsection


@push('scripts')

    @vite(['resources/js/historial.js'])

@endpush
