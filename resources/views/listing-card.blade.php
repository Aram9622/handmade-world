<article class="card" data-reveal>
    <a href="{{ $listing->publicUrl() }}">
        <div class="card-photo">
            <img src="{{ route('listings.photo', $listing) }}" alt="{{ $listing->title }}" loading="lazy">
        </div>
        <div class="card-copy">
            <span>{{ $cardCategory }}</span>
            <{{ $heading }}>{{ $listing->title }}</{{ $heading }}>
            <strong>@include('listing-price')</strong>
            @if($listing->city)
                <p class="contact">{{ $listing->city }}</p>
            @endif
            @if($showPublishedDate && $listing->created_at)
                <time class="published-date" datetime="{{ $listing->created_at->toAtomString() }}">{{ $listing->created_at->locale(app()->getLocale())->isoFormat('LL') }}</time>
            @endif
        </div>
    </a>
</article>
