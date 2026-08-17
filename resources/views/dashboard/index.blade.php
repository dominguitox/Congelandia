@extends('layouts.app')

@section('title', 'Inicio')

@push('css')
    @vite(['resources/css/dashboard.css'])
@endpush

@section('content')
    <div class="dashboard-grid">
        <p>Probando</p>
    </div>
@endsection