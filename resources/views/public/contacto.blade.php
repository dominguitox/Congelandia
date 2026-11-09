@extends('layouts.public')

@section('title', 'Contacto')
@section('descripcion', 'Contáctanos para consultar productos, precios y coordinar tu pedido. WhatsApp, teléfono, correo y dirección de Congelandia.')

@section('content')
    @php
        $contacto = config('public.contacto');
        $waNumero = preg_replace('/\D+/', '', (string) $contacto['whatsapp']);
        $waUrl = $waNumero
            ? 'https://wa.me/' . $waNumero . '?text=' . urlencode('Hola Congelandia, quiero hacer una consulta.')
            : null;
        $mapaUrl = 'https://www.google.com/maps/search/?api=1&query=' . urlencode($contacto['direccion']);
        $telefonoUrl = 'tel:' . preg_replace('/\s+/', '', $contacto['telefono']);
    @endphp

    <section class="pub-page-head">
        <div class="pub-container pub-page-head__inner">
            <div>
                <ul class="pub-breadcrumb">
                    <li><a href="{{ route('public.index') }}">Inicio</a></li>
                    <li>Contacto</li>
                </ul>

                <span class="pub-eyebrow">Contacto</span>
                <h1>Hablemos de tu pedido</h1>
                <p>
                    Escríbenos, llámanos o visítanos. Te ayudamos a elegir productos,
                    confirmar stock y coordinar la entrega.
                </p>
            </div>

            <div class="pub-page-head__stats">
                <div>
                    <strong>{{ $totalProductos }}</strong>
                    <span>productos</span>
                </div>
                <div>
                    <strong>100%</strong>
                    <span>cadena de frío</span>
                </div>
            </div>
        </div>
    </section>

    <section class="pub-section pub-section--tight">
        <div class="pub-container">
            <div class="pub-contact-grid">
                <div class="pub-card">
                    <h2>Datos de contacto</h2>

                    <ul class="pub-contact-list">
                        <li class="pub-contact-item">
                            <span class="pub-contact-item__icon">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none"
                                    stroke="currentColor" stroke-width="1.8" stroke-linecap="round"
                                    stroke-linejoin="round" aria-hidden="true">
                                    <path d="M22 16.9v3a2 2 0 0 1-2.2 2 19.8 19.8 0 0 1-8.6-3.1 19.5 19.5 0 0 1-6-6A19.8 19.8 0 0 1 2.1 4.2 2 2 0 0 1 4.1 2h3a2 2 0 0 1 2 1.7c.1.9.4 1.8.7 2.7a2 2 0 0 1-.5 2.1L8.1 9.7a16 16 0 0 0 6 6l1.2-1.2a2 2 0 0 1 2.1-.5c.9.3 1.8.6 2.7.7a2 2 0 0 1 1.9 2.2Z" />
                                </svg>
                            </span>
                            <div>
                                <span class="pub-contact-item__label">Teléfono</span>
                                <a class="pub-contact-item__value" href="{{ $telefonoUrl }}">
                                    {{ $contacto['telefono'] }}
                                </a>
                            </div>
                        </li>

                        @if($waUrl)
                            <li class="pub-contact-item">
                                <span class="pub-contact-item__icon">
                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor"
                                        aria-hidden="true">
                                        <path d="M12.04 2C6.5 2 2 6.5 2 12.04c0 1.77.46 3.5 1.34 5.02L2 22l5.08-1.33a10 10 0 0 0 4.96 1.3h.01C17.6 21.97 22 17.5 22 12.04 22 6.5 17.58 2 12.04 2Zm5.9 14.18c-.25.7-1.45 1.34-2 1.38-.55.05-1.07.24-3.6-.75-3.05-1.2-5-4.32-5.15-4.52-.15-.2-1.23-1.64-1.23-3.13 0-1.48.78-2.21 1.06-2.51.28-.3.61-.38.81-.38.2 0 .4 0 .58.01.18.01.43-.07.67.51.25.6.85 2.08.93 2.23.07.15.12.33.02.53-.1.2-.15.33-.3.5-.15.18-.31.4-.45.53-.15.15-.3.31-.13.61.17.3.76 1.26 1.63 2.04 1.12 1 2.06 1.31 2.36 1.46.3.15.47.13.65-.08.18-.2.75-.87.95-1.17.2-.3.4-.25.67-.15.28.1 1.75.83 2.05.98.3.15.5.23.57.35.08.13.08.73-.17 1.43Z" />
                                    </svg>
                                </span>
                                <div>
                                    <span class="pub-contact-item__label">WhatsApp</span>
                                    <a class="pub-contact-item__value" href="{{ $waUrl }}" target="_blank"
                                        rel="noopener">
                                        Escríbenos y te respondemos
                                    </a>
                                </div>
                            </li>
                        @endif

                        <li class="pub-contact-item">
                            <span class="pub-contact-item__icon">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none"
                                    stroke="currentColor" stroke-width="1.8" stroke-linecap="round"
                                    stroke-linejoin="round" aria-hidden="true">
                                    <rect x="2" y="4" width="20" height="16" rx="2" />
                                    <path d="m2 7 10 6 10-6" />
                                </svg>
                            </span>
                            <div>
                                <span class="pub-contact-item__label">Correo</span>
                                <a class="pub-contact-item__value" href="mailto:{{ $contacto['email'] }}">
                                    {{ $contacto['email'] }}
                                </a>
                            </div>
                        </li>

                        <li class="pub-contact-item">
                            <span class="pub-contact-item__icon">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none"
                                    stroke="currentColor" stroke-width="1.8" stroke-linecap="round"
                                    stroke-linejoin="round" aria-hidden="true">
                                    <path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z" />
                                    <circle cx="12" cy="10" r="3" />
                                </svg>
                            </span>
                            <div>
                                <span class="pub-contact-item__label">Ubicación</span>
                                <a class="pub-contact-item__value" href="{{ $mapaUrl }}" target="_blank"
                                    rel="noopener">
                                    {{ $contacto['direccion'] }}
                                </a>
                            </div>
                        </li>

                        <li class="pub-contact-item">
                            <span class="pub-contact-item__icon">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none"
                                    stroke="currentColor" stroke-width="1.8" stroke-linecap="round"
                                    stroke-linejoin="round" aria-hidden="true">
                                    <circle cx="12" cy="12" r="9" />
                                    <path d="M12 7v5l3 2" />
                                </svg>
                            </span>
                            <div>
                                <span class="pub-contact-item__label">Horario</span>
                                <span class="pub-contact-item__value">{{ $contacto['horario'] }}</span>
                            </div>
                        </li>
                    </ul>

                    <div class="pub-contact-actions">
                        @if($waUrl)
                            <a class="pub-btn pub-btn--accent pub-btn--block" href="{{ $waUrl }}" target="_blank"
                                rel="noopener">
                                Escribir por WhatsApp
                            </a>
                        @endif

                        <a class="pub-btn pub-btn--ghost pub-btn--block" href="mailto:{{ $contacto['email'] }}">
                            Enviar un correo
                        </a>
                    </div>
                </div>

                <div class="pub-contact-side">
                    <div class="pub-map">
                        <div>
                            <span class="pub-map__pin">
                                <svg width="26" height="26" viewBox="0 0 24 24" fill="none"
                                    stroke="currentColor" stroke-width="1.8" stroke-linecap="round"
                                    stroke-linejoin="round" aria-hidden="true">
                                    <path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z" />
                                    <circle cx="12" cy="10" r="3" />
                                </svg>
                            </span>

                            <h3>Visítanos</h3>
                            <p>{{ $contacto['direccion'] }}<br>{{ $contacto['horario'] }}</p>
                        </div>

                        <a class="pub-btn pub-btn--accent pub-btn--sm" href="{{ $mapaUrl }}" target="_blank"
                            rel="noopener">
                            Abrir en Google Maps
                        </a>
                    </div>

                    <div class="pub-card pub-contact-note">
                        <h2>Antes de venir</h2>
                        <p>
                            Escríbenos antes de acercarte para confirmar stock y horario de retiro.
                            Así te aseguramos que lo que buscas esté disponible.
                        </p>

                        @if($contacto['instagram'])
                            <a class="pub-link-arrow" href="{{ $contacto['instagram'] }}" target="_blank"
                                rel="noopener">
                                Revisar Instagram
                            </a>
                        @else
                            <a class="pub-link-arrow" href="{{ route('public.catalogo') }}">
                                Ver el catálogo
                                <svg width="15" height="15" viewBox="0 0 24 24" fill="none"
                                    stroke="currentColor" stroke-width="2.4" stroke-linecap="round"
                                    stroke-linejoin="round" aria-hidden="true">
                                    <path d="M5 12h14M13 6l6 6-6 6" />
                                </svg>
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="pub-section pub-section--alt">
        <div class="pub-container">
            <div class="pub-section-head">
                <div>
                    <span class="pub-eyebrow">Cómo comprar</span>
                    <h2>Tres pasos, sin vueltas</h2>
                    <p>Sin carritos ni registros: tú eliges, nosotros coordinamos.</p>
                </div>
            </div>

            <div class="pub-steps">
                <div class="pub-step">
                    <span class="pub-step__num">01</span>
                    <h3>Revisa el catálogo</h3>
                    <p>Consulta productos, precios y disponibilidad en el catálogo público.</p>
                </div>
                <div class="pub-step">
                    <span class="pub-step__num">02</span>
                    <h3>Escríbenos</h3>
                    <p>Envíanos tu lista por WhatsApp o correo y te confirmamos stock y total.</p>
                </div>
                <div class="pub-step">
                    <span class="pub-step__num">03</span>
                    <h3>Coordina la entrega</h3>
                    <p>Acordamos retiro en tienda o despacho según tu ubicación.</p>
                </div>
            </div>
        </div>
    </section>

    <section class="pub-section">
        <div class="pub-container">
            <div class="pub-section-head">
                <div>
                    <span class="pub-eyebrow pub-eyebrow--accent">Dudas frecuentes</span>
                    <h2>Preguntas antes de pedir</h2>
                    <p>Lo que más nos consultan por WhatsApp.</p>
                </div>
            </div>

            <div class="accordion pub-faq" id="pubFaq">
                <div class="accordion-item">
                    <h3 class="accordion-header">
                        <button class="accordion-button" type="button" data-bs-toggle="collapse"
                            data-bs-target="#faqUno" aria-expanded="true" aria-controls="faqUno">
                            ¿Cómo hago un pedido?
                        </button>
                    </h3>
                    <div id="faqUno" class="accordion-collapse collapse show" data-bs-parent="#pubFaq">
                        <div class="accordion-body">
                            Elige los productos en el catálogo y escríbenos por WhatsApp con tu lista.
                            Te confirmamos disponibilidad, precio total y forma de entrega.
                        </div>
                    </div>
                </div>

                <div class="accordion-item">
                    <h3 class="accordion-header">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                            data-bs-target="#faqDos" aria-expanded="false" aria-controls="faqDos">
                            ¿Los precios del catálogo son finales?
                        </button>
                    </h3>
                    <div id="faqDos" class="accordion-collapse collapse" data-bs-parent="#pubFaq">
                        <div class="accordion-body">
                            Los precios publicados son referenciales y pueden cambiar según stock y proveedor.
                            Al confirmar tu pedido te enviamos el total actualizado.
                        </div>
                    </div>
                </div>

                <div class="accordion-item">
                    <h3 class="accordion-header">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                            data-bs-target="#faqTres" aria-expanded="false" aria-controls="faqTres">
                            ¿Hacen despacho a domicilio?
                        </button>
                    </h3>
                    <div id="faqTres" class="accordion-collapse collapse" data-bs-parent="#pubFaq">
                        <div class="accordion-body">
                            Coordinamos retiro en tienda y despacho según tu comuna. Escríbenos con tu dirección
                            y te confirmamos disponibilidad y costo de entrega.
                        </div>
                    </div>
                </div>

                <div class="accordion-item">
                    <h3 class="accordion-header">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                            data-bs-target="#faqCuatro" aria-expanded="false" aria-controls="faqCuatro">
                            ¿Los productos llegan congelados?
                        </button>
                    </h3>
                    <div id="faqCuatro" class="accordion-collapse collapse" data-bs-parent="#pubFaq">
                        <div class="accordion-body">
                            Sí. Mantenemos cadena de frío hasta la entrega. Al recibir, guarda los productos
                            en el congelador lo antes posible.
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="pub-cta">
        <div class="pub-container">
            <div>
                <h2>¿Hacemos tu pedido?</h2>
                <p>Escríbenos y te ayudamos a elegir, confirmar stock y coordinar la entrega.</p>
            </div>

            <div class="pub-cta__actions">
                @if($waUrl)
                    <a class="pub-btn pub-btn--accent" href="{{ $waUrl }}" target="_blank" rel="noopener">
                        Hablar por WhatsApp
                    </a>
                @endif
                <a class="pub-btn pub-btn--ghost" href="{{ route('public.catalogo') }}">Ver catálogo</a>
            </div>
        </div>
    </section>
@endsection
