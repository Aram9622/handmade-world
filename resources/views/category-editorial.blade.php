@if($story['timeline'] || $story['works'])
<section class="editorial-history" aria-labelledby="history-heading">
    <h2 id="history-heading">{{ $story['history_title'] }}</h2>
    @if($story['timeline'])
        <ol class="timeline">
            @foreach($story['timeline'] as $entry)
                <li data-reveal>
                    <p class="timeline-period">{{ $entry['period'] }}</p>
                    <h3>{{ $entry['title'] }}</h3>
                    <p>{{ $entry['description'] }}</p>
                </li>
            @endforeach
        </ol>
    @endif
    @if($story['works'])
        <h3 class="inspiration-heading">{{ $story['gallery_title'] }}</h3>
        <div class="gallery">
            @foreach($story['works'] as $work)
                <figure data-reveal>
                    @if($work['image_url'])
                        <img src="{{ $work['image_url'] }}" alt="{{ __('Иллюстрация по мотивам работы :title', ['title' => $work['title']]) }}" loading="lazy">
                    @endif
                    <figcaption>
                        {{ $work['title'] }}
                        @if(!empty($work['url']))
                            <a class="text-link" href="{{ $work['url'] }}">{{ __('Смотреть оригинал') }} @if(!empty($work['museum'])) · {{ $work['museum'] }} @endif</a>
                        @elseif(!empty($work['museum']))
                            <span> · {{ $work['museum'] }}</span>
                        @endif
                    </figcaption>
                </figure>
            @endforeach
        </div>
    @endif
</section>
@endif
