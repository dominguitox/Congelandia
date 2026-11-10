@extends('layouts.app')
@section('title', 'Reportes')
@push('css')
    @vite(['resources/css/reportes.css'])
@endpush

@section('content')
@php
    $numero = fn ($valor) => number_format($valor, 0, ',', '.');
    $moneda = fn ($valor) => '$'.number_format($valor, 0, ',', '.');
    $filtros = ['seccion' => $seccion, 'fecha_inicio' => $fechaInicio, 'fecha_fin' => $fechaFin];
    $secciones = ['ventas' => 'Resumen de ventas', 'inventario' => 'Inventario y riesgo', 'habitos' => 'Hábitos de compra'];
@endphp
<div class="page reportes-page">
    <div class="page-header">
        <div class="page-title">
            <h1>Reportes</h1>
            <p>Una mirada al negocio para decidir qué vender, reponer y priorizar.</p>
        </div>
        <div class="page-actions">
            <a class="btn-outline" href="{{ route('reportes.index', $filtros + ['exportar' => 'csv']) }}">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M12 3v12m-4-4 4 4 4-4M5 16v5h14v-5"/></svg>
                Exportar CSV
            </a>
        </div>
    </div>

    <nav class="reportes-tabs" aria-label="Tipos de reporte">
        @foreach($secciones as $clave => $titulo)
            <a href="{{ route('reportes.index', array_replace($filtros, ['seccion' => $clave])) }}"
                class="reportes-tab {{ $seccion === $clave ? 'reportes-tab--active' : '' }}"
                @if($seccion === $clave) aria-current="page" @endif>{{ $titulo }}</a>
        @endforeach
    </nav>

    @if($errors->any())
        <div class="alert alert-danger" role="alert">
            @foreach($errors->all() as $error)<p class="mb-0">{{ $error }}</p>@endforeach
        </div>
    @endif

    @if($seccion !== 'inventario')
        <div class="panel reportes-filtro-panel">
            <div class="panel-body">
                <form method="GET" action="{{ route('reportes.index') }}" class="reportes-filtros">
                    <input type="hidden" name="seccion" value="{{ $seccion }}">
                    <div><label for="fecha_inicio" class="form-label">Desde</label><input class="form-control" type="date" name="fecha_inicio" id="fecha_inicio" value="{{ $fechaInicio }}" required></div>
                    <div><label for="fecha_fin" class="form-label">Hasta</label><input class="form-control" type="date" name="fecha_fin" id="fecha_fin" value="{{ $fechaFin }}" required></div>
                    <button class="btn-solid" type="submit">Aplicar período</button>
                    <a class="btn-soft" href="{{ route('reportes.index', ['seccion' => $seccion]) }}">Últimos 30 días</a>
                </form>
                <p class="reportes-nota">{{ \Carbon\Carbon::parse($fechaInicio)->format('d/m/Y') }} al {{ \Carbon\Carbon::parse($fechaFin)->format('d/m/Y') }} · Incluye ambos días. Valores en pesos chilenos.</p>
            </div>
        </div>
    @else
        <p class="reportes-contexto">Inventario actual · Actualizado al {{ now()->format('d/m/Y H:i') }} · No depende del período de ventas.</p>
    @endif

    @include('reportes.'.$seccion)
</div>
@endsection
