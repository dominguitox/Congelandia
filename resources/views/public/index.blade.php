<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Congelandia - El mundo de los congelados</title>

    {{-- Estilos y scripts generales --}} @vite(['resources/css/app.css', 'resources/js/app.js'])
    {{-- CSS específico de cada módulo --}} @stack('css')

    <style>
        .bg-teal-top {
            background-color: #008b9c;
        }

        .bg-teal-main {
            background-color: #00a2b8;
        }

        .text-teal {
            color: #00a2b8;
        }

        .bg-orange {
            background-color: #fd7e14;
        }

        .text-orange {
            color: #fd7e14;
        }

        .text-yellow {
            color: #ffc107;
        }

        .bg-yellow {
            background-color: #ffc107;
        }

        .search-bar {
            background-color: #f8f9fa;
            border: 1px solid #dee2e6;
        }
    </style>
</head>

{{-- Agregamos padding-top para que el contenido nunca quede oculto bajo la barra fija --}}

<body class="bg-white text-dark d-flex flex-column min-vh-100" style="padding-top: 45px;">

    {{-- Barra superior de sesión activa (SIEMPRE VISIBLE Y FIJA) --}}
    @if(auth()->check() || session()->has('usuario') || session()->has('user') || session()->has('idUsuario'))
        <div class="bg-dark text-white py-2 shadow-sm"
            style="font-size: 0.85rem; position: fixed; top: 0; left: 0; width: 100%; z-index: 1050;">
            <div class="container d-flex justify-content-between align-items-center">
                <span>Sesión iniciada como
                    <strong>{{ auth()->user()->nombre ?? session('usuario')->nombre ?? 'Usuario' }}</strong></span>
                <span>
                    {{-- Ruta corregida de tu panel interno --}}
                    <a class="btn bg-orange text-white rounded-pill fw-bold px-3 py-1" href="{{ route('dashboard.index') }}"
                        style="font-size: 0.85rem;">
                        ⚙️ Ir al Panel
                    </a>
                </span>
            </div>
        </div>
    @endif
    <!-- Top Bar[cite: 2] -->
    <div class="bg-teal-top text-white py-2" style="font-size: 0.85rem;">
        <div class="container d-flex justify-content-between align-items-center">
            <span>🚚 Delivery gratis en compras sobre $25.000 — Santiago RM</span>
            <span>🕒 Lun-Sáb 9–20h &nbsp;&nbsp; 📞 +56 9 1234 5678</span>
        </div>
    </div>

    <!-- Navegación Principal[cite: 2] -->
    <nav class="navbar navbar-expand-lg bg-white py-3 shadow-sm">
        <div class="container d-flex justify-content-between align-items-center">
            <!-- Logo -->
            <a class="navbar-brand text-dark d-flex align-items-center" href="/">
                <div class="bg-orange text-white rounded-circle d-flex justify-content-center align-items-center me-2"
                    style="width: 40px; height: 40px;">
                    ❄️
                </div>
                <div class="lh-1">
                    <span class="d-block fw-bold fs-5">CONGELANDIA</span>
                    <span class="text-teal fw-bold" style="font-size: 0.65rem; letter-spacing: 1px;">MINI
                        SUPERMERCADO</span>
                </div>
            </a>

            <!-- Buscador -->
            <form class="d-flex w-50 px-4">
                <input class="form-control rounded-pill search-bar px-4 py-2" type="search"
                    placeholder="🔍 ¿Qué estás buscando?">
            </form>

            <!-- Botón Carrito -->
            <button class="btn bg-orange text-white rounded-pill fw-bold px-4 py-2 border-0">
                🛒 Carrito
            </button>
        </div>
    </nav>

    <!-- === SECCIÓN HERO (Mantén la imagen publicitaria estática aquí) === -->
    <div class="container-fluid bg-teal-main py-5 d-flex align-items-center">
        <div class="container">
            <div class="row align-items-center">

                <!-- Columna Izquierda (Textos) -->
                <div class="col-md-6 text-white pe-5">
                    <!-- ... tus textos promocionales ... -->
                </div>

                <!-- Columna Derecha (Imagen Promocional Fija) -->
                <div class="col-md-6 position-relative mt-5 mt-md-0">
                    <div class="rounded-4 overflow-hidden shadow-lg" style="height: 500px;">
                        <!-- Usa una imagen genérica atractiva de tu carpeta public/images para la portada -->
                        <img src="{{ asset('images/portada-mariscos.jpg') }}" alt="Promoción"
                            style="width: 100%; height: 100%; object-fit: cover;">
                    </div>
                </div>

            </div>
        </div>
    </div>


    <!-- === NUEVA SECCIÓN: GRILLA DE PRODUCTOS DINÁMICOS === -->
    <div class="container my-5">
        <h2 class="fw-bold mb-4">Productos Destacados</h2>

        <!-- row crea la fila, y col-md-4 hace que quepan 3 tarjetas por fila en pantallas medianas/grandes -->
        <div class="row">
            @foreach($productos as $producto)

                <div class="col-md-4 mb-4">
                    <div class="card h-100 shadow-sm border-0 rounded-4 overflow-hidden">

                        <!-- Lógica de las imágenes -->
                        @if($producto->imagenes->count() > 0)
                            <img src="{{ asset($producto->imagenes->first()->rutaImagen) }}"
                                alt="{{ $producto->imagenes->first()->alt }}" class="card-img-top"
                                style="height: 250px; object-fit: cover;">
                        @else
                            <img src="{{ asset('images/default.png') }}" alt="Sin imagen" class="card-img-top"
                                style="height: 250px; object-fit: cover;">
                        @endif

                        <!-- Datos del producto -->
                        <div class="card-body text-center">
                            <h5 class="fw-bold text-dark">{{ $producto->nombre }}</h5>
                            <p class="text-teal fw-bold fs-4 mb-0">${{ $producto->precioActual->precioVenta ?? 0 }}</p>
                            <p class="text-muted small">Stock: {{ $producto->stock }}</p>

                            <button class="btn bg-orange text-white rounded-pill fw-bold w-100 mt-2">
                                Añadir al carrito
                            </button>
                        </div>

                    </div>
                </div>
            @endforeach
        </div>
    </div>

</body>

</html>