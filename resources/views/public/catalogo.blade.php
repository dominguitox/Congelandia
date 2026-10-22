@extends('layouts.public')

@section('title', 'Catálogo')

@section('content')
    <section class="bg-teal-main text-white py-5">
        <div class="container">
            <h1 class="fw-bold mb-2">Catálogo</h1>
            <p class="mb-0 opacity-75">Encuentra tus congelados favoritos y consulta disponibilidad.</p>
        </div>
    </section>

    <section class="container py-5">
        <form action="{{ route('public.catalogo') }}" method="GET" class="row g-2 align-items-end mb-4">
            <div class="col-lg-6">
                <label for="busqueda" class="form-label fw-semibold">Buscar producto</label>
                <input id="busqueda" type="search" name="q" value="{{ $busqueda }}"
                    class="form-control rounded-pill px-4" placeholder="Nombre o descripción">
            </div>

            <div class="col-sm-6 col-lg-4">
                <label for="categoria" class="form-label fw-semibold">Categoría</label>
                <select id="categoria" name="categoria" class="form-select rounded-pill px-4">
                    <option value="">Todas las categorías</option>
                    @foreach($categorias as $categoria)
                        <option value="{{ $categoria->idCategoria }}"
                            @selected((int) request('categoria') === $categoria->idCategoria)>
                            {{ $categoria->nombre }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-sm-6 col-lg-2 d-grid">
                <button type="submit" class="btn bg-orange text-white rounded-pill fw-bold">Filtrar</button>
            </div>
        </form>

        <div class="d-flex flex-wrap gap-2 mb-4">
            <a href="{{ route('public.catalogo', array_filter(['q' => $busqueda])) }}"
                class="btn btn-sm rounded-pill {{ !$categoriaActiva ? 'bg-teal text-white' : 'btn-outline-secondary' }}">
                Todas
            </a>

            @foreach($categorias as $categoria)
                <a href="{{ route('public.catalogo', array_filter(['q' => $busqueda, 'categoria' => $categoria->idCategoria])) }}"
                    class="btn btn-sm rounded-pill {{ $categoriaActiva?->idCategoria === $categoria->idCategoria ? 'bg-teal text-white' : 'btn-outline-secondary' }}">
                    {{ $categoria->nombre }}
                </a>
            @endforeach
        </div>

        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
            <p class="text-muted mb-0">
                {{ $productos->count() }} {{ $productos->count() === 1 ? 'producto encontrado' : 'productos encontrados' }}
                @if($categoriaActiva)
                    en <strong>{{ $categoriaActiva->nombre }}</strong>
                @endif
                @if($busqueda !== '')
                    para <strong>“{{ $busqueda }}”</strong>
                @endif
            </p>

            @if($busqueda !== '' || $categoriaActiva)
                <a href="{{ route('public.catalogo') }}" class="btn btn-sm btn-outline-secondary rounded-pill">
                    Limpiar filtros
                </a>
            @endif
        </div>

        <div class="row g-4">
            @forelse($productos as $producto)
                <div class="col-sm-6 col-lg-4 col-xl-3">
                    @include('public.partials.producto-card', ['producto' => $producto])
                </div>
            @empty
                <div class="col-12">
                    <div class="alert alert-light border text-center py-5 mb-0">
                        <h2 class="h5 fw-bold">No encontramos productos</h2>
                        <p class="text-muted mb-3">Prueba con otra búsqueda o revisa todas las categorías.</p>
                        <a href="{{ route('public.catalogo') }}" class="btn bg-orange text-white rounded-pill px-4">
                            Ver todo el catálogo
                        </a>
                    </div>
                </div>
            @endforelse
        </div>
    </section>
@endsection
