@extends('layouts.app')

@section('title', 'Reportes')

@push('css')
    @vite(['resources/css/reportes.css'])
@endpush

@section('content')
    <div class="dashboard-grid">
        <h2>Reportes</h2>
        <p>Este es el módulo de reportes</p>
    </div>
@endsection 