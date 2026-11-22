<div class="category-intro">
    @if($categoryMenu = $storefrontMenu->get('categories'))
        <div class="wrap">
            <nav class="category-nav" aria-label="{{ __('Категории каталога') }}">
                @foreach($categoryMenu as $item)
                    @php($target = $item['parameters']['category'] ?? null)
                    <a class="text-link" href="{{ $item['href'] }}" @if($target === $category) aria-current="page" @endif>{{ $item['text'] ?? $editorial[$target]['title'] }}</a>
                @endforeach
            </nav>
        </div>
    @endif
    <section class="hero editorial-hero full-cover category-cover editorial-{{ $category }}">
        <div class="hero-copy">
            <h1>{{ $categoryName }}</h1>
            <h2>{{ $story['heading'] }}</h2>
            <p>{{ $story['intro'] }}</p>
            <div class="category-actions">
                <a class="button" href="#catalog">{{ $story['cta'] }}</a>
                @include('category-subscription')
            </div>
        </div>
        <div class="cover-art">
            <img class="editorial-image" src="{{ $story['cover_url'] }}" alt="{{ $story['alt'] }}" fetchpriority="high">
        </div>
    </section>
</div>
