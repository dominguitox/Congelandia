<!DOCTYPE html>
<html>


<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <title>@yield('title', 'titulo de la pagina')</title>
</head>
<header>
    @include('partials.navbar')
    @include('partials.header')

</header>

<body>

    <main>
        @yield('content')
    </main>

</body>

</html>