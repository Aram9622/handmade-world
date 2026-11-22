<section class="category-directory" aria-labelledby="category-heading">
    <h2 id="category-heading">{{ $editorial['home']['directory_title'] }}</h2>
    <div class="home-collection">
        @foreach(array_diff_key($editorial, ['home' => true]) as $key => $label)
            <a class="category-tile" data-reveal href="{{ route('catalog', ['category' => $key]) }}">
                <img src="{{ $editorial[$key]['cover_url'] }}" alt="" loading="lazy">
                <span>{{ $editorial[$key]['title'] }}</span>
            </a>
        @endforeach
    </div>
</section>
