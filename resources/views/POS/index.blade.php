@extends('layouts.app')

@section('title', 'Sistema de ventas POS')

@push('css')
    @vite(['resources/css/pos.css'])
@endpush

@section('content')
    <div class="pos-grid">
        <div class="box">
            <div class="boxhead">
                <h2>Productos</h2>
            </div>
            <p>Lista de productos disponibles para venta.</p>
            <div class="productos-box">
                
            </div>
        </div>
        <div class="box">
            <div class="boxhead">

                <h2>Carrito de compras</h2>
            </div>
            <p>Lista de productos seleccionados para la venta.</p>

        </div>
    </div>
@endsection