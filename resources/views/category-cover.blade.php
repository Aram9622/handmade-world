<div class="category-intro">
    <div class="wrap">
        <nav class="category-nav" aria-label="{{ __('Категории каталога') }}">
            <a class="text-link" href="{{ route('catalog') }}">{{ __('Все категории') }}</a>
            @foreach(\App\Models\Listing::CATEGORIES as $key => $label)
                <a class="text-link" href="{{ route('catalog', ['category' => $key]) }}" @if($key === $category) aria-current="page" @endif>{{ __($label) }}</a>
            @endforeach
        </nav>
    </div>
    <section class="hero editorial-hero full-cover category-cover editorial-{{ $category }}">
        <div class="hero-copy">
            <h1>{{ $categoryName }}</h1>
            <h2>{{ __($story['heading']) }}</h2>
            <p>{{ __($story['intro']) }}</p>
            <a class="button" href="#catalog">{{ __('К объявлениям') }}</a>
        </div>
        <div class="cover-art">
            <img class="editorial-image" src="{{ asset('images/editorial/'.$category.'.webp') }}" alt="{{ __($story['alt']) }}" fetchpriority="high">
        </div>
    </section>
</div>
