@php($categoryMenu = $storefrontMenu->get('categories', collect()))
@if($category !== '' ? $categoryMenu->isNotEmpty() : $categoryMenu->contains(fn ($item) => isset($item['parameters']['category'])))
    <div class="wrap">
        <nav class="category-nav" aria-label="{{ __('Категории каталога') }}">
            @foreach($categoryMenu->sortBy(fn (array $item) => isset($item['parameters']['category'])) as $item)
                @php($target = $item['parameters']['category'] ?? null)
                <a class="text-link" href="{{ $item['href'] }}" @if($target === $category) aria-current="page" @endif>{{ $item['text'] ?? $editorial[$target]['title'] }}</a>
            @endforeach
        </nav>
    </div>
@endif
