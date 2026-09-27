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
                            <td class="ticket">
                                #{{ $salida->idSalida }}
                            </td>
                            <td>
                                {{ \Carbon\Carbon::parse($salida->fecha)->format('d M Y, H:i') }}
                            </td>
                            <td>
                                {{ $salida->cantidad ?? 0 }}
                            </td>
                            <td>
                                {{ $salida->usuario ?? 'Sin usuario' }}
                            </td>
                            <td class="total">
                                ${{ number_format($salida->totalSalida, 0, ',', '.') }}
                            </td>
                            <td>
                                <button class="btn-expand" type="button" onclick="toggleDetalle({{ $salida->idSalida }}, this)">
                                    ▼
                                </button>
                            </td>
                        </tr>
                        <tr id="detalle-{{ $salida->idSalida }}" class="detalle-row">
                            <td colspan="6">
                                <div class="detalle-container">
                                    <h4>
                                        Detalle de la Venta
                                    </h4>
                                    @foreach($salida->detalle as $producto)
                                        <div class="detalle-item">
                                            <span class="cantidad">
                                                {{ $producto->cantidad }}x
                                            </span>
                                            <span class="producto">
                                                {{ $producto->producto }}
                                            </span>
                                            <span class="precio-unitario">
                                                ${{ number_format($producto->precioUnitario, 0, ',', '.') }} c/u
                                            </span>
                                            <span class="subtotal">
                                                ${{ number_format($producto->subtotal, 0, ',', '.') }}
                                            </span>
                                        </div>
                                    @endforeach
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="empty-message">
                                No existen ventas registradas.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

@endsection