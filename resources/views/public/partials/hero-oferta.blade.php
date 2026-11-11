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
@endphp

<div class="{{ $principal ? 'pub-hero__photo' : 'pub-hero__float' }}">
    @if($src)
        <img src="{{ $src }}" alt="{{ $imagen->alt ?: $producto->nombre }}"
            width="{{ $principal ? 1000 : 860 }}" height="{{ $principal ? 1250 : 645 }}">
    @else
        <div class="pub-hero__placeholder" role="img" aria-label="Foto de {{ $producto->nombre }} próximamente">
            <span>Foto próximamente</span>
        </div>
    @endif

    <div class="{{ $principal ? 'pub-hero__offer-caption' : 'pub-hero__offer-label' }}">
        <strong>{{ $producto->nombre }}</strong>
        <span>-{{ number_format((float) $producto->mayor_descuento, 0) }}% de descuento</span>
    </div>
</div>
