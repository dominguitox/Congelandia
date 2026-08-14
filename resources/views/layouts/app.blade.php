<!DOCTYPE html>
<html>


<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <title>@yield('title', 'titulo de la pagina')</title>
</head>

<body>


    @include('partials.navbar')

    <main>
        <h1>@yield('title', 'titulo de la pagina')</h1>
        @yield('content')
    </main>

</body>

</html>