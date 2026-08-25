@extends('layouts.app')

@section('title', 'Sistema de ventas POS')

@push('css')
    @vite(['resources/css/pos.css'])
@endpush

@section('content')
    <div class="pos-grid">
        <div class="box inventario-productos">
            <div class="boxhead">
                <h2>Productos</h2>
                <p>Lista de productos disponibles para venta.</p>

            </div>
            <div class="productos-box">
                <p>*Productos*</p>
            </div>
        </div>
        <div class="box inventario-carrito">
            <div class="boxhead">
                <h2>Carrito de compras</h2>
                <p>Lista de productos seleccionados para la venta.</p>
            </div>
            <p>*Contenido*</p>
        </div>
    </div>
@endsection