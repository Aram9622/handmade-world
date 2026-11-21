<section class="category-directory" aria-labelledby="category-heading">
    <h2 id="category-heading">{{ __('Ремёсла и искусство') }}</h2>
    <div class="grid">
        @foreach(array_keys($editorial) as $key)
            <a class="category-tile" data-reveal href="{{ route('catalog', ['category' => $key]) }}">
                <img src="{{ asset('images/editorial/'.$key.'.webp') }}" alt="" loading="lazy">
                <span>{{ __(\App\Models\Listing::CATEGORIES[$key]) }}</span>
            </a>
        @endforeach
    </div>
</section>
