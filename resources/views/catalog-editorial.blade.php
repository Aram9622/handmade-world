<section class="hero editorial-hero">
    <div class="hero-copy">
        <h1>Сделано <em>руками.</em></h1>
        <p>Картины, керамика и вещи для дома от независимых мастеров.</p>
        <a class="button" href="#catalog-results">Смотреть новинки</a>
    </div>
    <img class="editorial-image" src="{{ asset('images/editorial/home.webp') }}" alt="Коллаж с руками мастера, работающего с глиной" fetchpriority="high">
</section>
<section class="category-directory" aria-labelledby="category-heading">
    <h2 id="category-heading">Ремёсла и искусство</h2>
    <div class="grid">
        @foreach(array_keys($editorial) as $key)
            <a class="category-tile" href="{{ route('catalog', ['category' => $key]) }}">
                <img src="{{ asset('images/editorial/'.$key.'.webp') }}" alt="" loading="lazy">
                <span>{{ \App\Models\Listing::CATEGORIES[$key] }}</span>
            </a>
        @endforeach
    </div>
</section>
