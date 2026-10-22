@extends('layouts.public')

@section('title', 'Contacto')

@section('content')
    @php($contacto = config('public.contacto'))

    <section class="bg-teal-main text-white py-5">
        <div class="container">
            <h1 class="fw-bold mb-2">Contacto</h1>
            <p class="mb-0 opacity-75">Estamos disponibles para ayudarte a elegir y coordinar tu pedido.</p>
        </div>
    </section>

    <section class="container py-5">
        <div class="row g-4">
            <div class="col-lg-5">
                <div class="bg-white rounded-4 shadow-sm p-4 h-100">
                    <h2 class="h4 fw-bold mb-3">Datos de contacto</h2>

                    <ul class="list-unstyled d-grid gap-3 mb-4">
                        <li>
                            <span class="d-block text-muted small">Teléfono</span>
                            <a class="link-dark fw-semibold text-decoration-none"
                                href="tel:{{ preg_replace('/\s+/', '', $contacto['telefono']) }}">
                                {{ $contacto['telefono'] }}
                            </a>
                        </li>
                        <li>
                            <span class="d-block text-muted small">Correo</span>
                            <a class="link-dark fw-semibold text-decoration-none"
                                href="mailto:{{ $contacto['email'] }}">
                                {{ $contacto['email'] }}
                            </a>
                        </li>
                        <li>
                            <span class="d-block text-muted small">Ubicación</span>
                            <a class="link-dark fw-semibold text-decoration-none" target="_blank" rel="noopener"
                                href="https://www.google.com/maps/search/?api=1&amp;query={{ urlencode($contacto['direccion']) }}">
                                {{ $contacto['direccion'] }}
                            </a>
                        </li>
                        <li>
                            <span class="d-block text-muted small">Horario de atención</span>
                            <span class="fw-semibold">{{ $contacto['horario'] }}</span>
                        </li>
                    </ul>

                    <div class="d-grid gap-2">
                        @if($contacto['whatsapp'])
                            <a class="btn bg-orange text-white rounded-pill fw-bold py-2" target="_blank" rel="noopener"
                                href="https://wa.me/{{ preg_replace('/\D+/', '', $contacto['whatsapp']) }}">
                                Escribir por WhatsApp
                            </a>
                        @endif
                        <a class="btn btn-outline-secondary rounded-pill fw-bold py-2"
                            href="mailto:{{ $contacto['email'] }}">
                            Enviar un correo
                        </a>
                    </div>
                </div>
            </div>

            <div class="col-lg-7">
                <div class="bg-white rounded-4 shadow-sm p-4 h-100">
                    <h2 class="h4 fw-bold mb-3">¿Cómo comprar?</h2>

                    <div class="d-grid gap-3">
                        <div class="d-flex gap-3">
                            <span class="public-step">1</span>
                            <div>
                                <h3 class="h6 fw-bold mb-1">Revisa el catálogo</h3>
                                <p class="text-muted mb-0">Consulta productos, precios y disponibilidad.</p>
                            </div>
                        </div>

                        <div class="d-flex gap-3">
                            <span class="public-step">2</span>
                            <div>
                                <h3 class="h6 fw-bold mb-1">Contáctanos</h3>
                                <p class="text-muted mb-0">Escríbenos por WhatsApp, correo o llámanos.</p>
                            </div>
                        </div>

                        <div class="d-flex gap-3">
                            <span class="public-step">3</span>
                            <div>
                                <h3 class="h6 fw-bold mb-1">Coordina tu pedido</h3>
                                <p class="text-muted mb-0">Acordamos el retiro o la entrega según tu ubicación.</p>
                            </div>
                        </div>
                    </div>

                    <hr class="my-4">

                    <p class="text-muted mb-3">
                        Si buscas un producto que no aparece en el catálogo, consúltanos y revisamos disponibilidad.
                    </p>

                    <a href="{{ route('public.catalogo') }}" class="btn bg-teal text-white rounded-pill fw-bold px-4">
                        Ver catálogo
                    </a>
                </div>
            </div>
        </div>
    </section>
@endsection
