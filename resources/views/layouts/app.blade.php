<!DOCTYPE html>
<html lang="es" data-theme="{{ config('theme.style') }}" data-color="{{ config('theme.color') }}">

<head>

    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>
        Congelandia - @yield('title', 'Inicio')
    </title>

    {{-- Estilos y scripts generales --}}
    @vite([
        'resources/css/app.css',
        'resources/js/app.js'
    ])

    @if(config('theme.style') === 'md3')
        @fonts('roboto')
    @endif

    {{-- CSS específico de cada módulo --}}
    @stack('css')
    @include('partials.theme')

</head>

<body class="bg-light">

    {{-- Mensajes de error --}}
    @if(session('error'))

        <div class="flash-error">

            <strong>Error:</strong>

            {{ session('error') }}

        </div>

    @endif


    {{-- Componentes generales --}}

    @include('partials.header')
    @include('partials.sidebar')

    {{-- Fondo para cerrar el menú lateral en móvil --}}
    <div class="sidebar-overlay" id="sidebarOverlay" aria-hidden="true"></div>


    {{-- Contenido principal --}}

    <main class="app-main">

        @yield('content')

    </main>


    @include('partials.footer')


    {{-- =====================================================
         SCRIPTS ESPECÍFICOS DE CADA MÓDULO
    ====================================================== --}}

    @stack('scripts')

</body>

</html>
