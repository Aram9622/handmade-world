@php
    $categoryMenu = $storefrontMenu->get('categories', collect())
        ->filter(fn (array $item) => !isset($item['parameters']['category']) || isset($populatedCategories[$item['parameters']['category']]));
@endphp
@if($categoryMenu->isNotEmpty())
    <div class="wrap">
        <nav class="category-nav" aria-label="{{ __('Категории каталога') }}">
            @foreach($categoryMenu->sortBy(fn (array $item) => isset($item['parameters']['category'])) as $item)
                @php($target = $item['parameters']['category'] ?? null)
                <a class="text-link" href="{{ $item['href'] }}" @if($target === $category) aria-current="page" @endif>{{ $item['text'] ?? $editorial[$target]['title'] }}</a>
            @endforeach
        </nav>
    </div>
@endif
