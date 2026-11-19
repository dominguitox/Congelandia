<nav class="reportes-cal-controles" aria-label="Paginación de {{ $etiqueta }}">
    <span>{{ $paginator->total() }} resultados · Página {{ $paginator->currentPage() }} de {{ $paginator->lastPage() }}</span>
    @if($paginator->previousPageUrl())<a class="btn-soft" href="{{ $paginator->previousPageUrl() }}" rel="prev">Anterior</a>@endif
    @if($paginator->nextPageUrl())<a class="btn-soft" href="{{ $paginator->nextPageUrl() }}" rel="next">Siguiente</a>@endif
</nav>
