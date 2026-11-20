<section class="hero editorial-hero">
    <div class="hero-copy">
        <h1>{{ __('Сделано') }} <em>{{ __('руками.') }}</em></h1>
        <p>{{ __('Картины, керамика и вещи для дома от независимых мастеров.') }}</p>
        <a class="button" href="#catalog-results">{{ __('Смотреть новинки') }}</a>
    </div>
    <img class="editorial-image" src="{{ asset('images/editorial/home.webp') }}" alt="{{ __('Коллаж с руками мастера, работающего с глиной') }}" fetchpriority="high">
</section>
<section class="category-directory" aria-labelledby="category-heading">
    <h2 id="category-heading">{{ __('Ремёсла и искусство') }}</h2>
    <div class="grid">
        @foreach(array_keys($editorial) as $key)
            <a class="category-tile" href="{{ route('catalog', ['category' => $key]) }}">
                <img src="{{ asset('images/editorial/'.$key.'.webp') }}" alt="" loading="lazy">
                <span>{{ __(\App\Models\Listing::CATEGORIES[$key]) }}</span>
            </a>
        @endforeach
    </div>
</section>
