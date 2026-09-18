<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Congelandia - @yield('title', 'Inicio')</title>

    <!-- Carga de Bootstrap 5 puro vía Vite -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-light">

    <!-- Inclusión de los módulos de la cabecera y menú lateral -->
    @include('partials.header')
    @include('partials.sidebar')

    <!-- Contenedor principal. El margin-top compensa los 60px del header fijo -->
    <main style="margin-top: 60px; margin-left: 250px; padding: 25px; min-height: calc(100vh - 60px);">
        @yield('content')
    </main>

    @include('partials.footer')


</body>

</html>