@extends('layout')
@php
    $description = $listing->formattedSalePrice().' · '.\Illuminate\Support\Str::limit(\Illuminate\Support\Str::squish($listing->description), 150);
@endphp
@section('title', $listing->title.' — купить изделие ручной работы | Handmade Work')
@section('description', $description)

@push('metadata')
    <link rel="canonical" href="{{ route('listings.show', $listing) }}">
    <meta property="og:type" content="website">
    <meta property="og:title" content="{{ $listing->title }}">
    <meta property="og:description" content="{{ $description }}">
    <meta property="og:image" content="{{ route('listings.photo', $listing) }}">
    <meta property="og:url" content="{{ route('listings.show', $listing) }}">
    <script type="application/ld+json">{!! \Illuminate\Support\Js::encode($product) !!}</script>
    <script src="{{ asset('listing-share.js') }}" defer></script>
@endpush

@section('content')
<a class="text-link back" href="{{ route('catalog') }}#catalog">К каталогу</a>
<article class="detail">
    <img src="{{ route('listings.photo', $listing) }}" alt="{{ $listing->title }}">
    <div class="detail-copy">
        <p class="category"><a class="text-link" href="{{ route('catalog', ['category' => $listing->category]) }}">{{ \App\Models\Listing::CATEGORIES[$listing->category] }}</a></p>
        <h1>{{ $listing->title }}</h1>
        <p class="price">@include('listing-price')</p>
        <dl class="fulfillment">
            @if($listing->city)
                <div><dt>Город</dt><dd>{{ $listing->city }}</dd></div>
            @endif
            <div>
                <dt>Получение</dt>
                <dd>{{ \App\Models\Listing::FULFILLMENT_METHODS[$listing->fulfillment_method] ?? 'По договорённости' }}</dd>
            </div>
        </dl>
        <p class="description">{{ $listing->description }}</p>
        <a class="button" href="mailto:{{ str_replace("%40", "@", rawurlencode($listing->contact_email)) }}?subject={{ rawurlencode($listing->title) }}">Написать продавцу</a>
        @if($listing->contact_phone)
            <p><a class="button outline" href="tel:{{ $listing->contact_phone }}">Позвонить {{ $listing->contact_phone }}</a></p>
        @endif
        <p class="contact">Договоритесь с мастером о получении и оплате.</p>
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
