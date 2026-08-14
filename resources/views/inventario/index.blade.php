@extends('layouts.app')

@section('title', 'Inventario')

@push('css')
    @vite(['resources/css/inventario.css'])
@endpush

@section('content')
    <div class="dashboard-grid">
    </div>
@endsection