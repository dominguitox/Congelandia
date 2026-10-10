<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Congelandia - El mundo de los congelados</title>

    {{-- Estilos y scripts generales --}}
    @vite([
        'resources/css/app.css',
        'resources/js/app.js'
    ])
    {{-- CSS específico de cada módulo --}}
    @stack('css')

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

<body>

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

    <!-- Hero Section[cite: 2] -->
    <div class="container-fluid bg-teal-main py-5 min-vh-100 d-flex align-items-center">
        <div class="container">
            <div class="row align-items-center">

                <!-- Columna Izquierda: Textos y Botones[cite: 2] -->
                <div class="col-md-6 text-white pe-5">
                    <span class="badge rounded-pill bg-white text-white bg-opacity-25 px-3 py-2 mb-4"
                        style="font-size: 0.9rem;">
                        ⚡ ¡Nuevas ofertas cada semana!
                    </span>

                    <h1 class="display-3 fw-bold mb-4" style="line-height: 1.1;">
                        Congelados,<br>
                        <span class="text-yellow">Mariscos</span><br>
                        y Carnes.
                    </h1>

                    <p class="fs-5 mb-5 opacity-75">
                        Todo lo que necesitas para tu cocina, al mejor precio. Delivery en 24 horas.
                    </p>

                    <div class="d-flex gap-3 mb-5">
                        <a href="#" class="btn bg-orange text-white btn-lg rounded-pill fw-bold px-4 py-3 border-0">VER
                            CATÁLOGO</a>
                        <a href="#"
                            class="btn bg-white text-teal btn-lg rounded-pill fw-bold px-4 py-3 border-0">OFERTAS
                            HOY</a>
                    </div>

                    <div class="d-flex gap-5 mt-4">
                        <div>
                            <h2 class="fw-bold mb-0">2.400+</h2>
                            <small class="opacity-75 fw-bold" style="font-size: 0.75rem;">CLIENTES</small>
                        </div>
                        <div>
                            <h2 class="fw-bold mb-0">24h</h2>
                            <small class="opacity-75 fw-bold" style="font-size: 0.75rem;">ENTREGA</small>
                        </div>
                        <div>
                            <h2 class="fw-bold mb-0">98%</h2>
                            <small class="opacity-75 fw-bold" style="font-size: 0.75rem;">SATISFACCIÓN</small>
                        </div>
                    </div>
                </div>

                <!-- Columna Derecha: Imagen y Tarjetas flotantes[cite: 2] -->
                <div class="col-md-6 position-relative mt-5 mt-md-0">
                    <!-- Contenedor de la imagen (reemplaza el src con la ruta real de tu imagen) -->
                    <div class="rounded-4 overflow-hidden shadow-lg" style="height: 500px;">
                        <img src="ruta_a_tu_imagen_de_mariscos.jpg" alt="Plato de mariscos"
                            style="width: 100%; height: 100%; object-fit: cover;">
                    </div>

                    <!-- Badge 30% Descuento[cite: 2] -->
                    <div class="position-absolute top-50 start-0 translate-middle bg-yellow text-dark p-3 rounded-4 shadow fw-bold text-center"
                        style="width: 120px;">
                        <span class="fs-1 d-block lh-1 mb-1">30%</span>
                        <span style="font-size: 0.75rem;">DESCUENTO</span>
                    </div>

                    <!-- Tarjeta de Producto Flotante[cite: 2] -->
                    <div class="position-absolute bottom-0 end-0 bg-white p-3 rounded-4 shadow m-4 text-dark"
                        style="width: 180px;">
                        <div class="text-orange mb-1" style="font-size: 0.8rem;">⭐⭐⭐⭐⭐</div>
                        <div class="fw-bold fs-6">Camarones Tigre</div>
                        <div class="text-teal fw-bold fs-5">$9.990</div>
                    </div>
                </div>

            </div>
        </div>
    </div>

</body>

</html>