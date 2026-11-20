<nav class="category-nav" aria-label="Категории каталога">
    <a class="text-link" href="{{ route('catalog') }}">Все категории</a>
    @foreach(\App\Models\Listing::CATEGORIES as $key => $label)
        <a class="text-link" href="{{ route('catalog', ['category' => $key]) }}" @if($key === $category) aria-current="page" @endif>{{ $label }}</a>
    @endforeach
</nav>
<section class="hero editorial-hero editorial-{{ $category }}">
    <div class="hero-copy">
        <h1>{{ $categoryName }}</h1>
        <h2>{{ $story['heading'] }}</h2>
        <p>{{ $story['intro'] }}</p>
        <a class="button" href="#catalog">К объявлениям</a>
    </div>
    <img class="editorial-image" src="{{ asset('images/editorial/'.$category.'.webp') }}" alt="{{ $story['alt'] }}" fetchpriority="high">
</section>
<section class="editorial-history" aria-labelledby="history-heading">
    <h2 id="history-heading">История раздела</h2>
    <ol class="timeline">
        @foreach($story['timeline'] as [$period, $title, $description])
            <li>
                <p class="timeline-period">{{ $period }}</p>
                <h3>{{ $title }}</h3>
                <p>{{ $description }}</p>
            </li>
        @endforeach
    </ol>
    @if(isset($story['works']))
        <h3 class="inspiration-heading">По мотивам известных работ</h3>
        <div class="gallery">
            @foreach($story['works'] as [$image, $title, $museum, $url])
                <figure>
                    <img src="{{ asset('images/editorial/'.$image) }}" alt="Иллюстрация по мотивам работы {{ $title }}" loading="lazy">
                    <figcaption>{{ $title }} <a class="text-link" href="{{ $url }}">Смотреть оригинал · {{ $museum }}</a></figcaption>
                </figure>
            @endforeach
        </div>
    @endif
</section>
