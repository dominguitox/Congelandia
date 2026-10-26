@extends('layouts.public')

@section('title', 'Inicio')

@section('content')
    <section class="bg-teal-main text-white py-5">
        <div class="container py-lg-4">
            <div class="row align-items-center g-5">
                <div class="col-lg-6">
                    <span class="hero-precio">Congelados de calidad</span>
                    <h1 class="hero-titulo">Listos para tu mesa, sin complicaciones</h1>
                    <p class="hero-subtitulo">
                        Descubre nuestra selección de empanadas, carnes, verduras y más.
                        Revisa el catálogo y contáctanos para coordinar tu pedido.
                    </p>

                    <div class="d-flex flex-wrap gap-3">
                        <a href="{{ route('public.catalogo') }}"
                            class="btn bg-orange text-white rounded-pill fw-bold px-4 py-2">
                            Ver catálogo
                        </a>
                        <a href="{{ route('public.contacto') }}"
                            class="btn btn-outline-light rounded-pill fw-bold px-4 py-2">
                            Contacto
                        </a>
                    </div>
                </div>

                <div class="col-lg-6">
                    <div class="rounded-4 overflow-hidden shadow-lg public-hero-img">
                        <img src="{{ asset('images/productos/1791667600_empanadas.jpg') }}"
                            alt="Empanadas congeladas Congelandia" class="w-100 h-100 object-fit-cover">
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="container py-5">
        <div class="row g-4 text-center">
            <div class="col-md-4">
                <div class="p-4 bg-white rounded-4 shadow-sm h-100">
                    <h2 class="h5 fw-bold mb-2">Calidad garantizada</h2>
                    <p class="text-muted mb-0">Productos seleccionados y conservados en cadena de frío.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="p-4 bg-white rounded-4 shadow-sm h-100">
                    <h2 class="h5 fw-bold mb-2">Precios accesibles</h2>
                    <p class="text-muted mb-0">Opciones para compartir en casa o en tu negocio.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="p-4 bg-white rounded-4 shadow-sm h-100">
                    <h2 class="h5 fw-bold mb-2">Atención directa</h2>
                    <p class="text-muted mb-0">Escríbenos o llámanos para resolver tus dudas.</p>
                </div>
            </div>
        </div>
    </section>

    <section class="container pb-5">
        <div class="d-flex flex-wrap justify-content-between align-items-end gap-2 mb-4">
            <div>
                <h2 class="fw-bold mb-1">Productos destacados</h2>
                <p class="text-muted mb-0">Una pequeña muestra de nuestro catálogo.</p>
            </div>
            <a href="{{ route('public.catalogo') }}" class="btn btn-outline-secondary rounded-pill px-4">
                Ver todo
            </a>
        </div>

        <div class="row g-4">
            @forelse($productos as $producto)
                <div class="col-sm-6 col-lg-4">
                    @include('public.partials.producto-card', ['producto' => $producto])
                </div>
            @empty
                <div class="col-12">
                    <div class="alert alert-light border text-center mb-0">
                        Todavía no hay productos publicados. Vuelve pronto.
                    </div>
                </div>
            @endforelse
        </div>
    </section>

    <section class="bg-teal-main text-white py-5">
        <div class="container d-flex flex-wrap justify-content-between align-items-center gap-4">
            <div>
                <h2 class="fw-bold mb-2">¿Buscas algo en particular?</h2>
                <p class="mb-0 opacity-75">Revisa el catálogo completo o contáctanos y te ayudamos.</p>
            </div>
            <div class="d-flex flex-wrap gap-3">
                <a href="{{ route('public.catalogo') }}"
                    class="btn bg-orange text-white rounded-pill fw-bold px-4 py-2">
                    Ir al catálogo
                </a>
                <a href="{{ route('public.contacto') }}"
                    class="btn btn-outline-light rounded-pill fw-bold px-4 py-2">
                    Hablar con nosotros
                </a>
            </div>
        </div>
    </section>
@endsection
