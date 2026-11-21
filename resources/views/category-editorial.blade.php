<section class="editorial-history" aria-labelledby="history-heading">
    <h2 id="history-heading">{{ __('История раздела') }}</h2>
    <ol class="timeline">
        @foreach($story['timeline'] as [$period, $title, $description])
            <li data-reveal>
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
                <figure data-reveal>
                    <img src="{{ asset('images/editorial/'.$image) }}" alt="{{ __('Иллюстрация по мотивам работы :title', ['title' => __($title)]) }}" loading="lazy">
                    <figcaption>{{ __($title) }} <a class="text-link" href="{{ $url }}">{{ __('Смотреть оригинал') }} · {{ __($museum) }}</a></figcaption>
                </figure>
            @endforeach
        </div>
    @endif
</section>
