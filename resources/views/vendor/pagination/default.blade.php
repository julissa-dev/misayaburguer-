@if ($paginator->hasPages())
    <nav class="pagination-saya-container" aria-label="Paginación de productos">
        <ul class="pagination-saya-list">
            {{-- Enlace Anterior --}}
            @if ($paginator->onFirstPage())
                <li class="pagination-saya-item disabled" aria-disabled="true">
                    <span class="pagination-saya-link pagination-prev-next">
                        <i class="fas fa-chevron-left"></i> Anterior
                    </span>
                </li>
            @else
                <li class="pagination-saya-item">
                    <a class="pagination-saya-link pagination-prev-next" href="{{ $paginator->previousPageUrl() }}" rel="prev">
                        <i class="fas fa-chevron-left"></i> Anterior
                    </a>
                </li>
            @endif

            {{-- Elementos de Paginación (Números y Elipsis) --}}
            @foreach ($elements as $element)
                {{-- Separador "..." --}}
                @if (is_string($element))
                    <li class="pagination-saya-item disabled" aria-disabled="true">
                        <span class="pagination-saya-link ellipsis">{{ $element }}</span>
                    </li>
                @endif

                {{-- Array de Enlaces --}}
                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <li class="pagination-saya-item active" aria-current="page">
                                <span class="pagination-saya-link current-page">{{ $page }}</span>
                            </li>
                        @else
                            <li class="pagination-saya-item">
                                <a class="pagination-saya-link" href="{{ $url }}">{{ $page }}</a>
                            </li>
                        @endif
                    @endforeach
                @endif
            @endforeach

            {{-- Enlace Siguiente --}}
            @if ($paginator->hasMorePages())
                <li class="pagination-saya-item">
                    <a class="pagination-saya-link pagination-prev-next" href="{{ $paginator->nextPageUrl() }}" rel="next">
                        Siguiente <i class="fas fa-chevron-right"></i>
                    </a>
                </li>
            @else
                <li class="pagination-saya-item disabled" aria-disabled="true">
                    <span class="pagination-saya-link pagination-prev-next">
                        Siguiente <i class="fas fa-chevron-right"></i>
                    </span>
                </li>
            @endif
        </ul>
        <p class="pagination-saya-info">Mostrando {{ $paginator->firstItem() }} al {{ $paginator->lastItem() }} de {{ $paginator->total() }} resultados</p>
    </nav>
@endif