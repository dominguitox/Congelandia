@extends('layouts.public')

@section('title', 'Catálogo')
@section('descripcion', 'Revisa el catálogo completo de Congelandia: empanadas, carnes, verduras, pescados y más congelados. Consulta precios y coordina tu pedido por WhatsApp.')

@section('content')
    @php
        $contacto = config('public.contacto');
        $waNumero = preg_replace('/\D+/', '', (string) $contacto['whatsapp']);
        $waUrl = $waNumero
            ? 'https://wa.me/' . $waNumero . '?text=' . urlencode('Hola Congelandia, quiero consultar disponibilidad.')
            : null;

        $filtrosBase = array_filter([
            'q' => $busqueda !== '' ? $busqueda : null,
            'orden' => $orden !== 'nombre' ? $orden : null,
        ]);

        $hayFiltros = $busqueda !== '' || $categoriaActiva || $orden !== 'nombre';

        $ordenes = [
            'nombre' => 'Nombre (A - Z)',
            'ofertas' => 'Ofertas primero',
            'precio-asc' => 'Menor precio',
            'precio-desc' => 'Mayor precio',
        ];
    @endphp

    <section class="pub-page-head">
        <div class="pub-container pub-page-head__inner">
            <div>
                <ul class="pub-breadcrumb">
                    <li><a href="{{ route('public.index') }}">Inicio</a></li>
                    <li>Catálogo</li>
                </ul>

                <span class="pub-eyebrow">Catálogo</span>
                <h1>Todo lo que tenemos congelado</h1>
                <p>
                    Busca por nombre, filtra por categoría y consulta el precio de cada producto.
                    Si algo no aparece, escríbenos y revisamos disponibilidad.
                </p>
            </div>

            <div class="pub-page-head__stats">
                <div>
                    <strong>{{ $totalProductos }}</strong>
                    <span>productos</span>
                </div>
                <div>
                    <strong>{{ $totalCategorias }}</strong>
                    <span>categorías</span>
                </div>
            </div>
        </div>
    </section>

    <section class="pub-section pub-section--tight">
        <div class="pub-container">
            <form class="pub-filters" action="{{ route('public.catalogo') }}" method="GET" role="search">
                @if($categoriaActiva)
                    <input type="hidden" name="categoria" value="{{ $categoriaActiva->idCategoria }}">
                @endif

                <div class="pub-filters__row">
                    <div class="pub-field pub-field--grow">
                        <label for="busqueda">Buscar producto</label>
                        <div class="pub-search pub-search--field">
                            <input id="busqueda" type="search" name="q" value="{{ $busqueda }}"
                                placeholder="Nombre o descripción del producto" autocomplete="off">
                            <button type="submit" aria-label="Buscar">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none"
                                    stroke="currentColor" stroke-width="2.4" stroke-linecap="round" aria-hidden="true">
                                    <circle cx="11" cy="11" r="7"></circle>
                                    <path d="m20 20-3.5-3.5"></path>
                                </svg>
                            </button>
                        </div>
                    </div>

                    <div class="pub-field">
                        <label for="orden">Ordenar por</label>
                        <select id="orden" name="orden" class="pub-select">
                            @foreach($ordenes as $valor => $etiqueta)
                                <option value="{{ $valor }}" @selected($orden === $valor)>{{ $etiqueta }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="pub-filters__actions">
                        <button class="pub-btn pub-btn--primary" type="submit">Aplicar</button>
                    </div>
                </div>

                <div class="pub-chips">
                    <span class="pub-chips__label">Categorías</span>

                    <a class="pub-filter-chip {{ !$categoriaActiva ? 'is-active' : '' }}"
                        href="{{ route('public.catalogo', $filtrosBase) }}">
                        Todas
                        <small>{{ $totalProductos }}</small>
                    </a>

                    @foreach($categorias as $categoria)
                        <a class="pub-filter-chip {{ $categoriaActiva?->idCategoria === $categoria->idCategoria ? 'is-active' : '' }}"
                            href="{{ route('public.catalogo', array_merge($filtrosBase, ['categoria' => $categoria->idCategoria])) }}">
                            {{ $categoria->nombre }}
                            <small>{{ $categoria->productos_count }}</small>
                        </a>
                    @endforeach
                </div>
            </form>

            <div class="pub-results-bar">
                <p>
                    <strong>{{ $productos->count() }}</strong>
                    {{ $productos->count() === 1 ? 'producto' : 'productos' }}
                    @if($categoriaActiva)
                        en <strong>{{ $categoriaActiva->nombre }}</strong>
                    @endif
                    @if($busqueda !== '')
                        para <strong>“{{ $busqueda }}”</strong>
                    @endif
                </p>

                @if($hayFiltros)
                    <a class="pub-clear" href="{{ route('public.catalogo') }}">Limpiar filtros</a>
                @endif
            </div>

            <div class="pub-products-grid pub-catalog__grid">
                @forelse($productos as $producto)
                    @include('public.partials.producto-card', ['producto' => $producto])
                @empty
                    <div class="pub-empty">
                        <div class="pub-empty__icon">
                            <svg width="34" height="34" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="1.6" stroke-linecap="round" aria-hidden="true">
                                <circle cx="11" cy="11" r="7"></circle>
                                <path d="m20 20-3.5-3.5"></path>
                            </svg>
                        </div>
                        <h2>No encontramos productos</h2>
                        <p>
                            Prueba con otra búsqueda o revisa todas las categorías.
                            También puedes escribirnos y lo buscamos por ti.
                        </p>

                        <div class="pub-empty__actions">
                            <a class="pub-btn pub-btn--primary" href="{{ route('public.catalogo') }}">
                                Ver todo el catálogo
                            </a>

                            @if($waUrl)
                                <a class="pub-btn pub-btn--ghost" href="{{ $waUrl }}" target="_blank" rel="noopener">
                                    Consultar por WhatsApp
                                </a>
                            @endif
                        </div>
                    </div>
                @endforelse
            </div>
        </div>
    </section>

    <section class="pub-cta">
        <div class="pub-container">
            <div>
                <h2>¿No encuentras lo que buscas?</h2>
                <p>Cuéntanos qué necesitas y te confirmamos disponibilidad, precio y forma de entrega.</p>
            </div>

            <div class="pub-cta__actions">
                @if($waUrl)
                    <a class="pub-btn pub-btn--accent" href="{{ $waUrl }}" target="_blank" rel="noopener">
                        Hablar por WhatsApp
                    </a>
                @endif
                <a class="pub-btn pub-btn--ghost" href="{{ route('public.contacto') }}">Ver contacto</a>
            </div>
        </div>
    </section>
@endsection
