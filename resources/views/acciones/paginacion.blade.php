@if($paginator->hasPages())
    <nav aria-label="Paginación del historial de acciones">
        <p class="text-muted small">Mostrando {{ $paginator->firstItem() }}–{{ $paginator->lastItem() }} de {{ $paginator->total() }} acciones</p>
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
                            <li class="page-item active"><span class="page-link" aria-current="page">{{ $page }}</span></li>
                        @else
                            <li class="page-item"><a class="page-link" href="{{ $url }}">{{ $page }}</a></li>
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
    </nav>
@endif
