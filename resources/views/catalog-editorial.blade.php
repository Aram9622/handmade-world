@if($populatedCategories)
<section class="category-directory" aria-labelledby="category-heading">
    <h2 id="category-heading">{{ $editorial['home']['directory_title'] }}</h2>
    <div class="home-collection">
        @foreach($populatedCategories as $key => $item)
            <a class="category-tile" data-reveal href="{{ route('catalog', ['category' => $key]) }}">
                <img src="{{ $item['cover_url'] }}" alt="" loading="lazy">
                <span>{{ $item['title'] }}</span>
            </a>
        @endforeach
    </div>
</section>
@else
    <div id="category-heading"></div>
@endif
