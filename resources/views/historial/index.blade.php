@extends('layouts.app')

@section('title', 'Historial de Ventas')

@push('css')
    @vite(['resources/css/historial.css'])
@endpush

@section('content')

    <div class="historial-container">

        <div class="historial-header">

            <div>
                <h2>Historial de Ventas</h2>
                <p>Revisa las transacciones pasadas</p>
            </div>

            <div class="historial-search">
                <input type="text" placeholder="Buscar por ID o cajero...">
            </div>
        </div>
        <div class="historial-card">
            <table class="historial-table">
                <thead>
                    <tr>
                        <th>ID Ticket</th>
                        <th>Fecha y Hora</th>
                        <th>Artículos</th>
                        <th>Cajero</th>
                        <th>Total</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($salidas as $salida)
                        <tr>
                            <td class="ticket">#{{ $salida->idSalida }}</td>
                            <td>{{ \Carbon\Carbon::parse($salida->fecha)->format('d M Y, H:i') }}</td>

                            {{-- Suma la cantidad total desde los detalles --}}
                            <td>{{ $salida->detalles->sum('cantidad') }}</td>

                            <td>{{ $salida->idUsuario ?? 'Sin usuario' }}</td>
                            <td class="total">${{ number_format($salida->totalSalida, 0, ',', '.') }}</td>
                            <td>
                                <button class="btn-expand" type="button"
                                    onclick="toggleDetalle({{ $salida->idSalida }}, this)">▼</button>
                            </td>
                        </tr>
                        <tr id="detalle-{{ $salida->idSalida }}" class="detalle-row">
                            <td colspan="6">
                                <div class="detalle-container">
                                    <h4>Detalle de la Venta</h4>

                                    @forelse($salida->detalles ?? [] as $item)
                                        <div class="detalle-item">
                                            <span class="cantidad">{{ $item->cantidad }}x</span>

                                            {{-- Accede al nombre del producto a través de la relación --}}
                                            <span class="producto">{{ $item->producto->nombre ?? 'Desconocido' }}</span>

                                            <span class="precio-unitario">
                                                ${{ number_format($item->precioCobrado, 0, ',', '.') }} c/u
                                            </span>

                                            <span class="subtotal">
                                                ${{ number_format($item->cantidad * $item->precioCobrado, 0, ',', '.') }}
                                            </span>
                                        </div>
                                    @empty
                                        <div class="detalle-item text-muted">No hay productos registrados.</div>
                                    @endforelse

                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="empty-message">No existen ventas registradas.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

@endsection