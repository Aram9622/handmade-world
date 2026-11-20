    @if($listings->hasPages())
        <nav class="pagination" aria-label="Страницы каталога">
            @if($listings->previousPageUrl())<a href="{{ $listings->previousPageUrl() }}{{ $anchor ?? '' }}">Назад</a>@endif
            <span>Страница {{ $listings->currentPage() }} из {{ $listings->lastPage() }}</span>
            @if($listings->nextPageUrl())<a href="{{ $listings->nextPageUrl() }}{{ $anchor ?? '' }}">Далее</a>@endif
        </nav>
    @endif
