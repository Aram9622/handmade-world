    @if($listings->hasPages())
        <nav class="pagination" aria-label="{{ __('Страницы каталога') }}">
            @if($listings->previousPageUrl())<a href="{{ $listings->previousPageUrl() }}{{ $anchor ?? '' }}">{{ __('Назад') }}</a>@endif
            <span>{{ __('Страница :current из :last', ['current' => $listings->currentPage(), 'last' => $listings->lastPage()]) }}</span>
            @if($listings->nextPageUrl())<a href="{{ $listings->nextPageUrl() }}{{ $anchor ?? '' }}">{{ __('Далее') }}</a>@endif
        </nav>
    @endif
