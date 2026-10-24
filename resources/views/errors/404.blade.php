@extends('layouts.public')

@section('content')
    <div class="container text-center py-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card shadow-sm border-0 p-5">
                    <div class="card-body">
                        <h1 class="display-1 text-danger fw-bold">404</h1>
                        <h3 class="mb-3">Página no encontrada</h3>
                        <p class="text-muted mb-4">
                            Lo sentimos, la página que estás buscando no existe, fue movida o ya no está disponible.
                        </p>
                        <a href="{{ url('/') }}" class="btn btn-primary px-4">
                            Volver al Inicio
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection