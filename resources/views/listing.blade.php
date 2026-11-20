@extends('layout')
@section('title', $listing->title.' — Handmade Work')

@section('content')
<a class="text-link back" href="{{ route('catalog') }}#catalog">К каталогу</a>
<article class="detail">
    <img src="{{ route('listings.photo', $listing) }}" alt="{{ $listing->title }}">
    <div class="detail-copy">
        <p class="category">{{ \App\Models\Listing::CATEGORIES[$listing->category] }}</p>
        <h1>{{ $listing->title }}</h1>
        <p class="price">{{ number_format($listing->price, 0, ',', ' ') }} ₽</p>
        <p class="description">{{ $listing->description }}</p>
        <a class="button" href="mailto:{{ str_replace("%40", "@", rawurlencode($listing->contact_email)) }}?subject={{ rawurlencode($listing->title) }}">Написать продавцу</a>
        <p class="contact">{{ $listing->contact_email }}</p>
        @if(auth()->id() === $listing->user_id)
            <form class="remove" action="{{ route('listings.destroy', $listing) }}" method="post" onsubmit="return confirm('Снять изделие с продажи?')">
                @csrf
                @method('DELETE')
                <button class="button outline" type="submit">Снять с продажи</button>
            </form>
        @endif
    </div>
</article>
@endsection
