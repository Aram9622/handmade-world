@extends('layout')
@section('title', $listing->title.' — Handmade Work')

@push('metadata')
    <link rel="canonical" href="{{ route('listings.show', $listing) }}">
    <meta property="og:type" content="website">
    <meta property="og:title" content="{{ $listing->title }}">
    <meta property="og:description" content="{{ $listing->formattedSalePrice() }} · {{ \Illuminate\Support\Str::limit($listing->description, 160) }}">
    <meta property="og:image" content="{{ route('listings.photo', $listing) }}">
    <meta property="og:url" content="{{ route('listings.show', $listing) }}">
    <script src="{{ asset('listing-share.js') }}" defer></script>
@endpush

@section('content')
<a class="text-link back" href="{{ route('catalog') }}#catalog">К каталогу</a>
<article class="detail">
    <img src="{{ route('listings.photo', $listing) }}" alt="{{ $listing->title }}">
    <div class="detail-copy">
        <p class="category">{{ \App\Models\Listing::CATEGORIES[$listing->category] }}</p>
        <h1>{{ $listing->title }}</h1>
        <p class="price">@include('listing-price')</p>
        <p class="description">{{ $listing->description }}</p>
        <a class="button" href="mailto:{{ str_replace("%40", "@", rawurlencode($listing->contact_email)) }}?subject={{ rawurlencode($listing->title) }}">Написать продавцу</a>
        <p class="contact">{{ $listing->contact_email }}</p>
        @include('listing-share')
        @can('update', $listing)
            <p><a class="button outline" href="{{ route('listings.edit', $listing) }}">Редактировать</a></p>
        @endcan
        @can('delete', $listing)
            <form class="remove" action="{{ route('listings.destroy', $listing) }}" method="post" onsubmit="return confirm('Снять изделие с продажи?')">
                @csrf
                @method('DELETE')
                <button class="button outline" type="submit">Снять с продажи</button>
            </form>
        @endcan
    </div>
</article>
@endsection
