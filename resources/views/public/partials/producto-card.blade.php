@php
    $imagen = $producto->imagenes->first();
    $ruta = $imagen?->rutaImagen;
    $existe = $ruta && file_exists(public_path($ruta));
    $optimizada = $existe
        ? 'images/productos/opt/' . pathinfo($ruta, PATHINFO_FILENAME) . '.webp'
        : null;
    $src = $optimizada && file_exists(public_path($optimizada))
        ? asset($optimizada)
        : ($existe ? asset($ruta) : null);

    $precio = $producto->precioActual?->precioVenta;
    $descuento = $producto->mayor_descuento ?? null;
    $categoria = $producto->categoria?->nombre ?? 'Sin categoría';

    $whatsapp = preg_replace('/\D+/', '', (string) config('public.contacto.whatsapp'));
    $consulta = $whatsapp
        ? 'https://wa.me/' . $whatsapp . '?text=' . urlencode(
            'Hola Congelandia, quiero consultar por ' . $producto->nombre . ' (' . $producto->codigo . ').'
        )
        : null;
@endphp

<article class="pub-product">
    <div class="pub-product__media {{ $src ? '' : 'pub-product__media--empty' }}">
        @if($src)
            <img src="{{ $src }}" alt="{{ $imagen->alt ?? $producto->nombre }}" width="900" height="675"
                loading="lazy">
        @else
            <div class="pub-product__empty" role="img" aria-label="Foto de {{ $producto->nombre }} próximamente">
                <svg width="46" height="46" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                    stroke-width="1.4" stroke-linecap="round" aria-hidden="true">
                    <path d="M12 2v20M4.2 6.5l15.6 11M19.8 6.5l-15.6 11" />
                    <path d="M12 5.5 9.8 3.8M12 5.5l2.2-1.7M12 18.5l-2.2 1.7M12 18.5l2.2 1.7" />
                    <path d="m5.6 8.4-2.7-.4M5.6 8.4l-.4-2.7M18.4 15.6l2.7.4M18.4 15.6l.4 2.7" />
                    <path d="m18.4 8.4 2.7-.4M18.4 8.4l.4-2.7M5.6 15.6l-2.7.4M5.6 15.6l-.4 2.7" />
                </svg>
                <span>Foto próximamente</span>
            </div>
        @endif

        @if($descuento !== null)
            <div class="pub-product__badges">
                <span></span>
                <span class="pub-offer">-{{ number_format((float) $descuento, 0) }}%</span>
            </div>
        @endif
    </div>

    <div class="pub-product__body">
        <p class="pub-product__cat">{{ $categoria }}</p>
        <h3 class="pub-product__title">{{ $producto->nombre }}</h3>

        @if($producto->descripcion)
            <p class="pub-product__desc">{{ $producto->descripcion }}</p>
        @endif

        <div class="pub-product__foot">
            @if($precio !== null && (float) $precio > 0)
                <span class="pub-price">
                    ${{ number_format((float) $precio, 0, ',', '.') }}
                    <small>CLP</small>
                </span>
            @else
                <span class="pub-price--pending">Precio por confirmar</span>
            @endif

            @if($consulta)
                <a class="pub-product__cta" href="{{ $consulta }}" target="_blank" rel="noopener"
                    aria-label="Consultar por {{ $producto->nombre }} en WhatsApp">
                    Consultar
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                        stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M5 12h14M13 6l6 6-6 6" />
                    </svg>
                </a>
            @endif
        </div>
    </div>
</article>
