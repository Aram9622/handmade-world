<nav class="category-nav" aria-label="{{ __('Категории каталога') }}">
    <a class="text-link" href="{{ route('catalog') }}">{{ __('Все категории') }}</a>
    @foreach(\App\Models\Listing::CATEGORIES as $key => $label)
        <a class="text-link" href="{{ route('catalog', ['category' => $key]) }}" @if($key === $category) aria-current="page" @endif>{{ __($label) }}</a>
    @endforeach
</nav>
<section class="hero editorial-hero editorial-{{ $category }}">
    <div class="hero-copy">
        <h1>{{ $categoryName }}</h1>
        <h2>{{ __($story['heading']) }}</h2>
        <p>{{ __($story['intro']) }}</p>
        <a class="button" href="#catalog">{{ __('К объявлениям') }}</a>
    </div>
    <img class="editorial-image" src="{{ asset('images/editorial/'.$category.'.webp') }}" alt="{{ __($story['alt']) }}" fetchpriority="high">
</section>
<section class="editorial-history" aria-labelledby="history-heading">
    <h2 id="history-heading">{{ __('История раздела') }}</h2>
    <ol class="timeline">
        @foreach($story['timeline'] as [$period, $title, $description])
            <li>
                <p class="timeline-period">{{ __($period) }}</p>
                <h3>{{ __($title) }}</h3>
                <p>{{ __($description) }}</p>
            </li>
        @endforeach
    </ol>
    @if(isset($story['works']))
        <h3 class="inspiration-heading">{{ __('По мотивам известных работ') }}</h3>
        <div class="gallery">
            @foreach($story['works'] as [$image, $title, $museum, $url])
                <figure>
                    <img src="{{ asset('images/editorial/'.$image) }}" alt="{{ __('Иллюстрация по мотивам работы :title', ['title' => __($title)]) }}" loading="lazy">
                    <figcaption>{{ __($title) }} <a class="text-link" href="{{ $url }}">{{ __('Смотреть оригинал') }} · {{ __($museum) }}</a></figcaption>
                </figure>
            @endforeach
        </div>
    @endif
</section>
