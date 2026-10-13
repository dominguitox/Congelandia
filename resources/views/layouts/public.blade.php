<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Congelandia - @yield('title', 'Inicio')</title>
    
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('css')

    <style>
        /* Celeste corporativo basado en el logo[cite: 2] */
        .bg-celeste { background-color: #00AEEF; } /* Ajusta el HEX al tono exacto de tu logo */
        .text-celeste { color: #00AEEF; }
    </style>
</head>

<body class="bg-white text-dark d-flex flex-column min-vh-100">

    {{-- Barra de Navegación Pública --}}
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark py-3">
        <div class="container">
            <a class="navbar-brand text-celeste fw-bold fs-4" href="{{ route('public.index') }}">
                🐧 Congelandia
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto fs-5">
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('public.index') }}">Inicio</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('public.catalogo') }}">Catálogo</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#">Contacto</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    {{-- Contenido Principal Centrado --}}
    <main class="container my-5 flex-grow-1">
        @yield('content')
    </main>

    {{-- Pie de Página Público --}}
    <footer class="bg-dark text-white text-center py-4 mt-auto">
        <div class="container">
            <p class="mb-1 text-celeste fw-bold">Congelandia - El Mundo de los Congelados</p>
            <p class="mb-0 text-secondary small">© {{ date('Y') }} Todos los derechos reservados.</p>
        </div>
    </footer>

</body>
</html>