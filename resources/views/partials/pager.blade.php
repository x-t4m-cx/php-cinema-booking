@if ($paginator->hasPages())
    <nav class="pager">
        @if ($paginator->onFirstPage())
            <span class="pager__link is-disabled">← Назад</span>
        @else
            <a class="pager__link" href="{{ $paginator->previousPageUrl() }}">← Назад</a>
        @endif

        <span class="pager__info">Страница {{ $paginator->currentPage() }} из {{ $paginator->lastPage() }}</span>

        @if ($paginator->hasMorePages())
            <a class="pager__link" href="{{ $paginator->nextPageUrl() }}">Вперёд →</a>
        @else
            <span class="pager__link is-disabled">Вперёд →</span>
        @endif
    </nav>
@endif
