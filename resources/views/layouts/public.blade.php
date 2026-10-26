<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    @php
        $estilos = [
            'fresco' => 'Fresco',
            'feria' => 'Feria',
            'hielo' => 'Hielo',
        ];

        $estiloSesion = session('public_estilo', config('public.estilo_default', 'fresco'));

        // "mercado" fue la primera propuesta; ahora se llama "feria".
        if ($estiloSesion === 'mercado') {
            $estiloSesion = 'feria';
        }

        $estiloQuery = request()->query('estilo');

        if ($estiloQuery === 'mercado') {
            $estiloQuery = 'feria';
        }

        if (is_string($estiloQuery) && array_key_exists($estiloQuery, $estilos)) {
            session(['public_estilo' => $estiloQuery]);
            $estiloSesion = $estiloQuery;
        }

        $estilo = array_key_exists($estiloSesion, $estilos) ? $estiloSesion : 'fresco';

        if (request()->has('selector')) {
            session(['public_selector' => request()->boolean('selector')]);
        }

        $mostrarSelector = config('public.selector_estilos', false) && session('public_selector', true);

        $contacto = config('public.contacto');
        $whatsappNumero = preg_replace('/\D+/', '', (string) $contacto['whatsapp']);
        $whatsappUrl = $whatsappNumero
            ? 'https://wa.me/' . $whatsappNumero . '?text=' . urlencode('Hola Congelandia, quiero hacer una consulta.')
            : null;
    @endphp

    <title>Congelandia - @yield('title', 'El mundo de los congelados')</title>
    <meta name="description" content="@yield('descripcion', 'Congelados de calidad para tu mesa: empanadas, carnes, verduras y más. Revisa el catálogo y coordina tu pedido.')">
    <meta name="theme-color" content="#082b45">

    <meta property="og:type" content="website">
    <meta property="og:site_name" content="Congelandia">
    <meta property="og:title" content="Congelandia - @yield('title', 'El mundo de los congelados')">
    <meta property="og:description" content="@yield('descripcion', 'Congelados de calidad para tu mesa. Revisa el catálogo y coordina tu pedido.')">
    <meta property="og:image" content="{{ asset('images/hero/tomates.webp') }}">
    <meta property="og:url" content="{{ url()->current() }}">

    <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">

    @fonts
    @vite([
        'resources/css/app.css',
        'resources/css/public.css',
        "resources/css/public-temas/{$estilo}.css",
        'resources/js/app.js',
    ])

    @stack('css')

    @php($conSesion = auth()->check() || session()->has('usuario') || session()->has('user') || session()->has('idUsuario'))
</head>

<body class="pub-body d-flex flex-column min-vh-100 {{ $conSesion ? 'public-con-sesion' : '' }} {{ $mostrarSelector ? 'has-stylebar' : '' }}" data-estilo="{{ $estilo }}">
    @php($usuarioSesion = session('usuario'))
    @php($nombreSesion = auth()->user()->nombre ?? (is_object($usuarioSesion) ? ($usuarioSesion->nombre ?? 'Usuario') : 'Usuario'))

    @if($conSesion)
        <div class="pub-session-bar">
            <div class="pub-container">
                <a class="pub-btn pub-btn--accent pub-btn--sm" href="{{ route('dashboard.index') }}">
                    Ir al panel
                </a>

                @if(isset($version) && $version)
                    <div class="d-none d-md-flex align-items-center gap-2 small">
                        <span><strong>Versión:</strong> {{ $version->numero }}</span>
                        <a href="{{ url('/version-info') }}" target="_blank" class="text-decoration-underline">Ver detalles</a>
                    </div>
                @endif

                <span class="small text-truncate">
                    <span class="d-none d-sm-inline">Sesión iniciada como </span>
                    <strong>{{ $nombreSesion }}</strong>
                </span>
            </div>
        </div>

        <div class="public-session-spacer" aria-hidden="true"></div>
    @endif

    <div class="pub-topbar">
        <div class="pub-container">
            <span class="pub-topbar__group">
                <span class="pub-topbar__hide-sm">Cadena de frío garantizada</span>
                <span class="pub-topbar__dot pub-topbar__hide-sm" aria-hidden="true">•</span>
                <span>Despacho coordinado</span>
            </span>

            <span class="pub-topbar__group">
                <span>{{ $contacto['horario'] }}</span>
                <a href="tel:{{ preg_replace('/\s+/', '', $contacto['telefono']) }}">
                    {{ $contacto['telefono'] }}
                </a>
            </span>
        </div>
    </div>

    <nav class="pub-nav">
        <div class="pub-container">
            <a class="pub-brand" href="{{ route('public.index') }}">
                <span class="pub-brand__mark">
                    <img src="{{ asset('images/brand/logo-marca.webp') }}" alt="" width="56" height="56">
                </span>
                <span class="pub-brand__text">
                    <span class="pub-brand__name">Congelandia</span>
                    <span class="pub-brand__tag">El mundo de los congelados</span>
                </span>
            </a>

            <button class="pub-nav__toggle" type="button" data-bs-toggle="collapse"
                data-bs-target="#pubNav" aria-controls="pubNav" aria-expanded="false"
                aria-label="Abrir navegación">
                <span></span>
            </button>

            <div class="collapse pub-nav__collapse" id="pubNav">
                <ul class="pub-nav__links">
                    <li>
                        <a class="{{ request()->routeIs('public.index') ? 'is-active' : '' }}"
                            href="{{ route('public.index') }}">Inicio</a>
                    </li>
                    <li>
                        <a class="{{ request()->routeIs('public.catalogo') ? 'is-active' : '' }}"
                            href="{{ route('public.catalogo') }}">Catálogo</a>
                    </li>
                    <li>
                        <a class="{{ request()->routeIs('public.contacto') ? 'is-active' : '' }}"
                            href="{{ route('public.contacto') }}">Contacto</a>
                    </li>
                </ul>

                <div class="pub-nav__actions">
                    <form class="pub-search" action="{{ route('public.catalogo') }}" method="GET" role="search">
                        <input type="search" name="q"
                            value="{{ request()->routeIs('public.catalogo') ? request('q') : '' }}"
                            placeholder="Buscar productos" aria-label="Buscar productos">
                        <button type="submit" aria-label="Buscar">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor" stroke-width="2.4" stroke-linecap="round">
                                <circle cx="11" cy="11" r="7"></circle>
                                <path d="m20 20-3.5-3.5"></path>
                            </svg>
                        </button>
                    </form>

                    @if($whatsappUrl)
                        <a class="pub-btn pub-btn--accent pub-btn--sm" href="{{ $whatsappUrl }}" target="_blank"
                            rel="noopener">
                            <svg width="17" height="17" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                                <path d="M12.04 2C6.5 2 2 6.5 2 12.04c0 1.77.46 3.5 1.34 5.02L2 22l5.08-1.33a10 10 0 0 0 4.96 1.3h.01C17.6 21.97 22 17.5 22 12.04 22 6.5 17.58 2 12.04 2Zm5.9 14.18c-.25.7-1.45 1.34-2 1.38-.55.05-1.07.24-3.6-.75-3.05-1.2-5-4.32-5.15-4.52-.15-.2-1.23-1.64-1.23-3.13 0-1.48.78-2.21 1.06-2.51.28-.3.61-.38.81-.38.2 0 .4 0 .58.01.18.01.43-.07.67.51.25.6.85 2.08.93 2.23.07.15.12.33.02.53-.1.2-.15.33-.3.5-.15.18-.31.4-.45.53-.15.15-.3.31-.13.61.17.3.76 1.26 1.63 2.04 1.12 1 2.06 1.31 2.36 1.46.3.15.47.13.65-.08.18-.2.75-.87.95-1.17.2-.3.4-.25.67-.15.28.1 1.75.83 2.05.98.3.15.5.23.57.35.08.13.08.73-.17 1.43Z" />
                            </svg>
                            <span class="d-none d-xl-inline">Pedir por WhatsApp</span>
                            <span class="d-xl-none">WhatsApp</span>
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </nav>

    <main class="pub-main flex-grow-1">
        @yield('content')
    </main>

    <footer class="pub-footer">
        <div class="pub-container">
            <div class="pub-footer__grid">
                <div>
                    <a class="pub-footer__brand" href="{{ route('public.index') }}">
                        <img src="{{ asset('images/brand/logo-marca.webp') }}" alt="" width="72"
                            height="72">
                        <span>
                            <strong>Congelandia</strong>
                            <span>El mundo de los congelados</span>
                        </span>
                    </a>

                    <p>
                        Congelados de calidad para compartir en casa o en tu negocio.
                        Revisa el catálogo y coordina tu pedido con nosotros.
                    </p>
                </div>

                <div>
                    <h2>Navegación</h2>
                    <ul>
                        <li><a href="{{ route('public.index') }}">Inicio</a></li>
                        <li><a href="{{ route('public.catalogo') }}">Catálogo</a></li>
                        <li><a href="{{ route('public.contacto') }}">Contacto</a></li>
                    </ul>
                </div>

                <div>
                    <h2>Contacto</h2>
                    <ul>
                        <li>
                            <a href="tel:{{ preg_replace('/\s+/', '', $contacto['telefono']) }}">
                                {{ $contacto['telefono'] }}
                            </a>
                        </li>
                        <li>
                            <a href="mailto:{{ $contacto['email'] }}">{{ $contacto['email'] }}</a>
                        </li>
                        <li>{{ $contacto['direccion'] }}</li>
                        <li>{{ $contacto['horario'] }}</li>
                        @if($contacto['instagram'])
                            <li>
                                <a href="{{ $contacto['instagram'] }}" target="_blank" rel="noopener">Instagram</a>
                            </li>
                        @endif
                    </ul>
                </div>
            </div>

            <div class="pub-footer__bottom">
                <span>&copy; {{ date('Y') }} Congelandia. Todos los derechos reservados.</span>
                @if(isset($version) && $version)
                    <span>Versión {{ $version->numero }}</span>
                @endif
            </div>
        </div>
    </footer>

    @if($mostrarSelector)
        <div class="pub-stylebar" role="group" aria-label="Vista previa de estilos">
            <span class="pub-stylebar__label">Vista previa</span>

            @foreach($estilos as $clave => $nombre)
                <a class="pub-stylebar__btn {{ $estilo === $clave ? 'is-active' : '' }}"
                    href="{{ request()->fullUrlWithQuery(['estilo' => $clave]) }}"
                    @if($estilo === $clave) aria-current="true" @endif>
                    {{ $nombre }}
                </a>
            @endforeach

            <a class="pub-stylebar__close" href="{{ request()->fullUrlWithQuery(['selector' => 0]) }}"
                aria-label="Ocultar selector de estilos">&times;</a>
        </div>
    @endif

    @stack('scripts')
</body>

</html>
