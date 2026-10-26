@extends('layouts.public')

@section('title', 'Inicio')
@section('descripcion', 'Congelados de calidad para tu mesa: empanadas, carnes, verduras y más. Revisa el catálogo y coordina tu pedido por WhatsApp.')

@section('content')
    @php
        $contacto = config('public.contacto');
        $waNumero = preg_replace('/\D+/', '', (string) $contacto['whatsapp']);
        $waUrl = $waNumero
            ? 'https://wa.me/' . $waNumero . '?text=' . urlencode('Hola Congelandia, quiero hacer un pedido.')
            : null;
    @endphp

    <section class="pub-hero">
        <div class="pub-container pub-hero__grid">
            <div class="pub-hero__copy">
                <span class="pub-eyebrow">El mundo de los congelados</span>

                <h1 class="pub-hero__title">
                    Comida real, <em>lista</em> en minutos.
                </h1>

                <p class="pub-hero__lead">
                    Empanadas, carnes, verduras y más, conservados en cadena de frío
                    para que solo tengas que calentar y disfrutar.
                </p>

                <div class="pub-hero__actions">
                    <a class="pub-btn pub-btn--primary" href="{{ route('public.catalogo') }}">
                        Ver catálogo
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="M5 12h14M13 6l6 6-6 6" />
                        </svg>
                    </a>

                    @if($waUrl)
                        <a class="pub-btn pub-btn--accent" href="{{ $waUrl }}" target="_blank" rel="noopener">
                            Pedir por WhatsApp
                        </a>
                    @endif
                </div>

                <ul class="pub-hero__trust">
                    <li>
                        <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="1.8" stroke-linecap="round" aria-hidden="true">
                            <path d="M12 2v20M4.2 6.5l15.6 11M19.8 6.5l-15.6 11" />
                        </svg>
                        Cadena de frío
                    </li>
                    <li>
                        <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2Z" />
                        </svg>
                        Pedidos por WhatsApp
                    </li>
                    <li>
                        <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="M3 7h11v10H3zM14 10h4l3 3v4h-7z" />
                            <circle cx="7" cy="18" r="1.6" />
                            <circle cx="18" cy="18" r="1.6" />
                        </svg>
                        Retiro y despacho
                    </li>
                </ul>

                <div class="pub-hero__stats">
                    <div>
                        <strong>{{ $totalProductos }}</strong>
                        <span>productos</span>
                    </div>
                    <div>
                        <strong>{{ $categorias->count() }}</strong>
                        <span>categorías</span>
                    </div>
                    <div>
                        <strong>100%</strong>
                        <span>cadena de frío</span>
                    </div>
                </div>
            </div>

            <div class="pub-hero__media">
                <div class="pub-hero__photo">
                    <img src="{{ asset('images/hero/tomates.webp') }}" alt="Tomates frescos de Congelandia"
                        width="1000" height="1250">
                </div>

                <div class="pub-hero__float">
                    <img src="{{ asset('images/hero/empanadas.webp') }}" alt="Empanadas de queso listas para freír"
                        width="860" height="1120">
                    <strong>Empanadas de queso</strong>
                    <span>Listas para freír</span>
                </div>

                <div class="pub-sticker" aria-hidden="true">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                        stroke-width="1.8" stroke-linecap="round">
                        <path d="M12 2v20M4.2 6.5l15.6 11M19.8 6.5l-15.6 11" />
                    </svg>
                    <span>Cadena<br>de frío</span>
                </div>
            </div>
        </div>
    </section>

    <section class="pub-benefits">
        <div class="pub-container">
            <div class="pub-benefits__grid">
                <div class="pub-benefit">
                    <span class="pub-benefit__icon">
                        <svg width="23" height="23" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="1.6" stroke-linecap="round" aria-hidden="true">
                            <path d="M12 2v20M4.2 6.5l15.6 11M19.8 6.5l-15.6 11" />
                            <path d="M12 5.5 9.8 3.8M12 5.5l2.2-1.7M12 18.5l-2.2 1.7M12 18.5l2.2 1.7" />
                        </svg>
                    </span>
                    <div>
                        <h3>Del congelador a tu mesa</h3>
                        <p>Productos conservados en cadena de frío hasta el momento de la entrega.</p>
                    </div>
                </div>

                <div class="pub-benefit">
                    <span class="pub-benefit__icon">
                        <svg width="23" height="23" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="M20.8 4.6a5.5 5.5 0 0 0-7.8 0L12 5.7l-1.1-1.1a5.5 5.5 0 0 0-7.8 7.8l1.1 1.1L12 21l7.7-7.5 1.1-1.1a5.5 5.5 0 0 0 0-7.8Z" />
                        </svg>
                    </span>
                    <div>
                        <h3>Selección para todos los días</h3>
                        <p>Opciones para compartir en casa, preparar algo rápido o abastecer tu negocio.</p>
                    </div>
                </div>

                <div class="pub-benefit">
                    <span class="pub-benefit__icon">
                        <svg width="23" height="23" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2Z" />
                        </svg>
                    </span>
                    <div>
                        <h3>Compra simple y directa</h3>
                        <p>Escríbenos por WhatsApp y coordinamos retiro o entrega según tu ubicación.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="pub-section">
        <div class="pub-container">
            <div class="pub-section-head">
                <div>
                    <span class="pub-eyebrow">Categorías</span>
                    <h2>Explora por categoría</h2>
                    <p>Encuentra más rápido eso que necesitas para esta semana.</p>
                </div>
                <a class="pub-link-arrow" href="{{ route('public.catalogo') }}">
                    Ver catálogo completo
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                        stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M5 12h14M13 6l6 6-6 6" />
                    </svg>
                </a>
            </div>

            <div class="pub-cats">
                @foreach($categorias as $categoria)
                    <a class="pub-cat"
                        href="{{ route('public.catalogo', ['categoria' => $categoria->idCategoria]) }}">
                        <span class="pub-cat__icon">
                            @include('public.partials.categoria-icono', ['nombre' => $categoria->nombre])
                        </span>
                        <span class="pub-cat__name">{{ $categoria->nombre }}</span>
                        <span class="pub-cat__count">{{ $categoria->productos_count }}</span>
                    </a>
                @endforeach
            </div>
        </div>
    </section>

    <section class="pub-section pub-section--alt">
        <div class="pub-container">
            <div class="pub-section-head">
                <div>
                    <span class="pub-eyebrow pub-eyebrow--accent">Selección</span>
                    <h2>Para llenar tu congelador</h2>
                    <p>Una muestra de lo que puedes encontrar en el catálogo.</p>
                </div>
                <a class="pub-link-arrow" href="{{ route('public.catalogo') }}">
                    Ver todo
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                        stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M5 12h14M13 6l6 6-6 6" />
                    </svg>
                </a>
            </div>

            <div class="pub-products-grid">
                @forelse($productos as $producto)
                    @include('public.partials.producto-card', ['producto' => $producto])
                @empty
                    <div class="pub-empty" style="grid-column: 1 / -1;">
                        <div class="pub-empty__icon">
                            <svg width="34" height="34" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="1.6" stroke-linecap="round" aria-hidden="true">
                                <path d="M12 2v20M4.2 6.5l15.6 11M19.8 6.5l-15.6 11" />
                            </svg>
                        </div>
                        <h2>Todavía no hay productos publicados</h2>
                        <p>Estamos preparando el catálogo. Vuelve pronto o escríbenos para consultar disponibilidad.</p>
                        @if($waUrl)
                            <a class="pub-btn pub-btn--primary" href="{{ $waUrl }}" target="_blank" rel="noopener">
                                Consultar por WhatsApp
                            </a>
                        @endif
                    </div>
                @endforelse
            </div>
        </div>
    </section>

    <section class="pub-section">
        <div class="pub-container">
            <div class="pub-section-head">
                <div>
                    <span class="pub-eyebrow">Así de simple</span>
                    <h2>Comprar es cosa de tres pasos</h2>
                    <p>Sin carritos ni registros: tú eliges, nosotros coordinamos.</p>
                </div>
            </div>

            <div class="pub-steps">
                <div class="pub-step">
                    <span class="pub-step__num">01</span>
                    <h3>Revisa el catálogo</h3>
                    <p>Encuentra los productos que necesitas y consulta precios y disponibilidad.</p>
                </div>
                <div class="pub-step">
                    <span class="pub-step__num">02</span>
                    <h3>Escríbenos</h3>
                    <p>Envíanos tu pedido por WhatsApp o correo. Te confirmamos stock y total.</p>
                </div>
                <div class="pub-step">
                    <span class="pub-step__num">03</span>
                    <h3>Coordina la entrega</h3>
                    <p>Acordamos retiro en tienda o despacho según tu ubicación.</p>
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
                <a class="pub-btn pub-btn--ghost" href="{{ route('public.contacto') }}">
                    Ver contacto
                </a>
            </div>
        </div>
    </section>
@endsection
