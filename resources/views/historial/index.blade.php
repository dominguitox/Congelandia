@extends('layouts.app')

@section('title', 'Historial')

@push('css')
    @vite(['resources/css/reportes.css'])
@endpush

@section('content')
    <div class="">
        <div>
            <h2>Historial</h2>
            <p>Revisa las transacciones pasadas</p>
        </div>
        <div class="buscador">

        </div>
        <!-- Tabla de historial -->
        <table class="table">
            <thead>
                <tr>
                    <th scope="col">Id </th>
                    <th scope="col">Fecha y hora</th>
                    <th scope="col">Artículos</th>
                    <th scope="col">Tipo de movimiento</th>
                    <th scope="col">Usuario</th>
                    <th scope="col">Cliente</th>
                    <th scope="col">Total </th>

                </tr>
            </thead>
            <tbody>
                @foreach($salidas as $salida)
                    <tr>
                        <td>{{ $salida->idSalida ?? 'Id' }}</td>
                        <td>{{ $salida->fecha ?? 'Sin proveedor' }}</td>
                        <td>{{ $salida->articulos ?? 'Articulos' }}</td>

                        <td>{{ $salida->idTipo ?? 'Sin categoría' }}</td>
                        <td>{{ $salida->idUsuario ?? 'No registrado' }}</td>
                        <td>{{ $salida->rutCliente ?? 'No registrado' }}</td>
                        <td>{{ $salida->totalSalida ?? '$' }}</td>

                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    </div>
@endsection