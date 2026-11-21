<div class="category-intro">
    <div class="wrap">
        <nav class="category-nav" aria-label="{{ __('Категории каталога') }}">
            <a class="text-link" href="{{ route('catalog') }}">{{ __('Все категории') }}</a>
            @foreach(\App\Models\Listing::CATEGORIES as $key => $label)
                <a class="text-link" href="{{ route('catalog', ['category' => $key]) }}" @if($key === $category) aria-current="page" @endif>{{ $editorial[$key]['title'] }}</a>
            @endforeach
        </nav>
    </div>
    <section class="hero editorial-hero full-cover category-cover editorial-{{ $category }}">
        <div class="hero-copy">
            <h1>{{ $categoryName }}</h1>
            <h2>{{ $story['heading'] }}</h2>
            <p>{{ $story['intro'] }}</p>
            <a class="button" href="#catalog">{{ $story['cta'] }}</a>
        </div>
        <div class="cover-art">
            <img class="editorial-image" src="{{ $story['cover_url'] }}" alt="{{ $story['alt'] }}" fetchpriority="high">
        </div>
    </section>
</div>
