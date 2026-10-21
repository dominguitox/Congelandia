@php
    $imagen = $producto->imagenes->first();
    $stock = max(0, (int) ($producto->total_ingresos ?? 0) - (int) ($producto->total_salidas ?? 0));
    $precio = $producto->precioActual?->precioVenta;
    $categoria = $producto->categoria?->nombre ?? 'Sin categoría';
@endphp

<article class="card h-100 shadow-sm border-0 rounded-4 overflow-hidden producto-publico">
@if($imagen)
    <img src="{{ asset($imagen->rutaImagen) }}" alt="{{ $imagen->alt ?? $producto->nombre }}"
        class="card-img-top public-producto-img" loading="lazy">
@else
    <div class="card-img-top public-producto-img d-flex align-items-center justify-content-center bg-light"
        role="img" aria-label="Producto sin imagen">
        <svg width="140" height="105" viewBox="0 0 800 600" aria-hidden="true">
            <g fill="none" stroke="#009fc3" stroke-width="18" stroke-linecap="round">
                <path d="M400 180v240M296 240l208 120M504 240L296 360" />
                <path d="M400 180l-40 40M400 180l40 40M400 420l-40-40M400 420l40-40" />
                <path d="M296 240l-56-8M296 240l-8-56M504 360l56 8M504 360l8 56" />
                <path d="M504 240l56-8M504 240l8-56M296 360l-56 8M296 360l-8 56" />
            </g>
        </svg>
    </div>
@endif

    <div class="card-body d-flex flex-column">
        <span class="badge rounded-pill text-bg-light align-self-start mb-2">{{ $categoria }}</span>
        <h3 class="h5 fw-bold text-dark mb-1">{{ $producto->nombre }}</h3>

        @if($producto->descripcion)
            <p class="text-muted small mb-3">{{ \Illuminate\Support\Str::limit($producto->descripcion, 90) }}</p>
        @endif

        <div class="mt-auto d-flex justify-content-between align-items-end gap-2">
            <div>
                @if($precio !== null && (float) $precio > 0)
                    <span class="text-teal fw-bold fs-4">${{ number_format((float) $precio, 0, ',', '.') }}</span>
                    <small class="text-muted">CLP</small>
                @else
                    <span class="text-muted fw-semibold">Precio por confirmar</span>
                @endif
            </div>

            <span class="badge rounded-pill {{ $stock > 0 ? 'text-bg-success' : 'text-bg-secondary' }}">
                {{ $stock > 0 ? 'Stock: ' . $stock : 'Sin stock' }}
            </span>
        </div>
    </div>
</article>
