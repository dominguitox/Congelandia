@if($paginator->hasPages())
    <nav aria-label="{{ $nombre }}">
        <p class="text-muted small">Mostrando {{ $paginator->firstItem() }}–{{ $paginator->lastItem() }} de {{ $paginator->total() }} {{ $etiqueta }} · Página {{ $paginator->currentPage() }} de {{ $paginator->lastPage() }}</p>
        <div class="d-flex align-items-end gap-3 flex-wrap">
        <ul class="pagination flex-wrap mb-0">
            @if($paginator->onFirstPage())
                <li class="page-item disabled"><span class="page-link" aria-disabled="true">Anterior</span></li>
            @else
                <li class="page-item"><a class="page-link" href="{{ $paginator->previousPageUrl() }}" rel="prev">Anterior</a></li>
            @endif
            @foreach($elements as $element)
                @if(is_string($element))
                    <li class="page-item disabled"><span class="page-link">{{ $element }}</span></li>
                @else
                    @foreach($element as $page => $url)
                        @if($page === $paginator->currentPage())
                            <li class="page-item active"><span class="page-link" aria-current="page"><span class="visually-hidden">Página </span>{{ $page }}</span></li>
                        @else
                            <li class="page-item"><a class="page-link" href="{{ $url }}"><span class="visually-hidden">Página </span>{{ $page }}</a></li>
                        @endif
                    @endforeach
                @endif
            @endforeach
            @if($paginator->hasMorePages())
                <li class="page-item"><a class="page-link" href="{{ $paginator->nextPageUrl() }}" rel="next">Siguiente</a></li>
            @else
                <li class="page-item disabled"><span class="page-link" aria-disabled="true">Siguiente</span></li>
            @endif
        </ul>
        @php($campoPagina = 'pagina-'.$etiqueta)
        <form method="GET" action="{{ $paginator->path() }}" aria-labelledby="{{ $campoPagina }}-label" class="d-flex align-items-end gap-2 flex-wrap">
            @foreach(request()->query() as $clave => $valor)
                @if($clave !== $paginator->getPageName() && is_scalar($valor))
                    <input type="hidden" name="{{ $clave }}" value="{{ $valor }}">
                @endif
            @endforeach
            <div>
                <label id="{{ $campoPagina }}-label" for="{{ $campoPagina }}" class="form-label mb-1">Ir a página</label>
                <input id="{{ $campoPagina }}" class="form-control" type="number" name="{{ $paginator->getPageName() }}"
                    value="{{ $paginator->currentPage() }}" min="1" max="{{ $paginator->lastPage() }}" step="1" required
                    inputmode="numeric" aria-describedby="{{ $campoPagina }}-limite" style="width: 7rem">
            </div>
            <span id="{{ $campoPagina }}-limite" class="pb-2">de {{ $paginator->lastPage() }}</span>
            <button type="submit" class="btn-outline">Ir</button>
        </form>
        </div>
    </nav>
@endif
