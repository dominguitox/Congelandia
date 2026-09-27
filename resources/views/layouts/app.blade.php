<!DOCTYPE html>
<html lang="es">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>
        Congelandia - @yield('title', 'Inicio')
    </title>


    <!-- Carga principal de estilos y scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])


    <!-- Carga de CSS específico de cada módulo -->
    @stack('css')


</head>


<body class="bg-light">


    <!-- Mensajes de error -->
    @if (session('error'))

        <div
            style="
                background-color: #f8d7da;
                color:#842029;
                padding: 1rem;
                border-radius: 5px;
                margin-bottom: 1rem;
                position: absolute;
                left: 40px;
                bottom: 20px;
                z-index: 9999;
                max-width: 60%;
            "
        >

            <strong>Error:</strong>
            {{ session('error') }}

        </div>

    @endif



    <!-- Componentes generales -->

    @include('partials.header')

    @include('partials.sidebar')



    <!-- Contenido principal -->

    <main
        style="
            margin-top: 60px;
            margin-left: 250px;
            padding: 25px;
            min-height: calc(100vh - 60px);
        "
    >

        @yield('content')

    </main>



    @include('partials.footer')


</body>

</html>