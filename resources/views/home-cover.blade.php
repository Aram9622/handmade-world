@include('category-nav')
<section class="hero editorial-hero full-cover home-cover">
    <div class="hero-copy">
        <h1>{{ $editorial['home']['title'] }} <em>{{ $editorial['home']['heading'] }}</em></h1>
        <p>{{ $editorial['home']['intro'] }}</p>
        <a class="button" href="#catalog-results">{{ $editorial['home']['cta'] }}</a>
    </div>
    <div class="cover-art">
        <img class="editorial-image" src="{{ $editorial['home']['cover_url'] }}" alt="{{ $editorial['home']['alt'] }}" fetchpriority="high">
    </div>
</section>
