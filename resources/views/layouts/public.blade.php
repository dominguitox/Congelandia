<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Congelandia - @yield('title', 'El mundo de los congelados')</title>

    @vite(['resources/css/app.css', 'resources/css/public.css', 'resources/js/app.js'])

    @php($temaActivo = config('app.tema'))
    @if($temaActivo && $temaActivo !== 'base' && file_exists(resource_path("css/theme/themes/{$temaActivo}.css")))
        @vite(["resources/css/theme/themes/{$temaActivo}.css"])
    @endif

    @stack('css')
</head>

<body class="bg-white text-dark d-flex flex-column min-vh-100">
    @php($contacto = config('public.contacto'))
    @php($usuarioSesion = session('usuario'))
    @php($nombreSesion = auth()->user()->nombre ?? (is_object($usuarioSesion) ? ($usuarioSesion->nombre ?? 'Usuario') : 'Usuario'))

    @if(auth()->check() || session()->has('usuario') || session()->has('user') || session()->has('idUsuario'))
        <div class="public-session-bar bg-dark text-white py-2">
            <div class="container d-flex flex-wrap justify-content-between align-items-center gap-2">
                <a class="btn btn-sm bg-orange text-white rounded-pill fw-bold px-3" href="{{ route('dashboard.index') }}">
                    Ir al panel
                </a>

                <div class="d-flex align-items-center gap-3 small">
                    @if(isset($version) && $version)
                        <span><strong>Versión:</strong> {{ $version->numero }}</span>
                        <a href="{{ url('/version-info') }}" target="_blank" class="text-white">Ver detalles</a>
                    @endif
                    <span>
                        Sesión iniciada como
                        <strong>{{ $nombreSesion }}</strong>
                    </span>
                </div>
            </div>
        </div>
    @endif

    <div class="bg-teal-top text-white py-2 public-topbar">
        <div class="container d-flex flex-wrap justify-content-between align-items-center gap-2">
            <span>Productos congelados listos para tu mesa</span>
            <span class="d-flex flex-wrap gap-3">
                <span>{{ $contacto['horario'] }}</span>
                <a href="tel:{{ preg_replace('/\s+/', '', $contacto['telefono']) }}" class="text-white">
                    {{ $contacto['telefono'] }}
                </a>
            </span>
        </div>
    </div>

    <nav class="navbar navbar-expand-lg bg-white py-3 shadow-sm public-navbar">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center text-dark" href="{{ route('public.index') }}">
                <img src="{{ asset('logo.png') }}" alt="Congelandia" class="public-logo me-2">
                <span class="lh-1">
                    <span class="d-block fw-bold fs-5">CONGELANDIA</span>
                    <span class="text-teal fw-bold public-tagline">EL MUNDO DE LOS CONGELADOS</span>
                </span>
            </a>

            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarPublico"
                aria-controls="navbarPublico" aria-expanded="false" aria-label="Abrir navegación">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarPublico">
                <form class="d-flex public-search mx-lg-auto my-3 my-lg-0" action="{{ route('public.catalogo') }}"
                    method="GET" role="search">
                    <input class="form-control rounded-pill search-bar px-4" type="search" name="q"
                        value="{{ request()->routeIs('public.catalogo') ? request('q') : '' }}"
                        placeholder="Buscar productos" aria-label="Buscar productos">
                    <button class="btn bg-orange text-white rounded-pill fw-bold ms-2 px-3" type="submit">
                        Buscar
                    </button>
                </form>

                <ul class="navbar-nav align-items-lg-center gap-lg-2">
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('public.index') ? 'active fw-bold' : '' }}"
                            href="{{ route('public.index') }}">Inicio</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('public.catalogo') ? 'active fw-bold' : '' }}"
                            href="{{ route('public.catalogo') }}">Catálogo</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('public.contacto') ? 'active fw-bold' : '' }}"
                            href="{{ route('public.contacto') }}">Contacto</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <main class="flex-grow-1">
        @yield('content')
    </main>

    <footer class="bg-dark text-white pt-5 pb-4 mt-auto public-footer">
        <div class="container">
            <div class="row g-4">
                <div class="col-lg-5">
                    <div class="d-flex align-items-center mb-3">
                        <img src="{{ asset('logo.png') }}" alt="Congelandia" class="public-logo me-2">
                        <span class="fs-5 fw-bold">CONGELANDIA</span>
                    </div>
                    <p class="text-white-50 mb-0">
                        Congelados de calidad para compartir en casa. Revisa nuestro catálogo y contáctanos para
                        coordinar tu pedido.
                    </p>
                </div>

                <div class="col-6 col-lg-3">
                    <h2 class="h6 text-uppercase text-white-50 mb-3">Navegación</h2>
                    <ul class="list-unstyled d-grid gap-2 mb-0">
                        <li><a class="link-light text-decoration-none" href="{{ route('public.index') }}">Inicio</a></li>
                        <li><a class="link-light text-decoration-none" href="{{ route('public.catalogo') }}">Catálogo</a></li>
                        <li><a class="link-light text-decoration-none" href="{{ route('public.contacto') }}">Contacto</a></li>
                    </ul>
                </div>

                <div class="col-6 col-lg-4">
                    <h2 class="h6 text-uppercase text-white-50 mb-3">Contacto</h2>
                    <ul class="list-unstyled d-grid gap-2 mb-0 text-white-50">
                        <li>
                            <a class="link-light text-decoration-none"
                                href="tel:{{ preg_replace('/\s+/', '', $contacto['telefono']) }}">
                                {{ $contacto['telefono'] }}
                            </a>
                        </li>
                        <li>
                            <a class="link-light text-decoration-none" href="mailto:{{ $contacto['email'] }}">
                                {{ $contacto['email'] }}
                            </a>
                        </li>
                        <li>{{ $contacto['direccion'] }}</li>
                        <li>{{ $contacto['horario'] }}</li>
                    </ul>
                </div>
            </div>

            <hr class="border-secondary my-4">

            <div class="d-flex flex-wrap justify-content-between gap-2 small text-white-50">
                <span>&copy; {{ date('Y') }} Congelandia. Todos los derechos reservados.</span>
                @if(isset($version) && $version)
                    <span>Versión {{ $version->numero }}</span>
                @endif
            </div>
        </div>
    </footer>

    @stack('scripts')
</body>

</html>
