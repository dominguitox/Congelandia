@extends('layouts.app')

@section('title', 'Panel General')

@section('content')
    <div class="container-fluid">
        <h2 class="mb-4">Resumen de actividad de hoy</h2>

        <div class="row">
            <!-- Tarjeta de Bootstrap para métricas -->

            <!-- Tarjeta  Ingresos Hoy -->
            <div class="col-md-3 col-sm-6 mb-3">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-body text-center">
                        <h5 class="card-title text-muted">Ingresos de hoy</h5>
                        <p class="card-text display-6 fw-bold text-success">$0</p>
                    </div>
                </div>
            </div>

            <!-- Tarjeta Órdenes Hoy -->
            <div class="col-md-3 col-sm-6 mb-3">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-body text-center">
                        <h5 class="card-title text-muted">Órdenes</h5>
                        <p class="card-text display-6 fw-bold">0</p>
                    </div>
                </div>
            </div>
            <!-- Tarjeta Productos vendidos Hoy -->
            <div class="col-md-3 col-sm-6 mb-3">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-body text-center">
                        <h5 class="card-title text-muted">Productos vendidos</h5>
                        <p class="card-text display-6 fw-bold">0</p>
                    </div>
                </div>
            </div>
            <!-- Tarjeta Alertas -->
            <div class="col-md-3 col-sm-6 mb-3">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-body text-center">
                        <h5 class="card-title text-muted">Alertas</h5>
                        <p class="card-text display-6 fw-bold">0</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <!-- Log Ventas recientes -->
            <div class="col-md-6 col-sm-6 mb-3">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-body text-center">
                        <h5 class="card-title text-muted">Ingresos de hoy</h5>
                        <p class="card-text display-6 fw-bold text-success">$0</p>
                    </div>
                </div>

            </div>
            <!-- Alerta Inventario bajo -->
            <div class="col-md-6 col-sm-6 mb-3">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-body text-center">
                        <h5 class="card-title text-muted">Ingresos de hoy</h5>
                        <p class="card-text display-6 fw-bold text-success">$0</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    </div>
@endsection