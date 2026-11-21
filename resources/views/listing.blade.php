@extends('layout')
@php
    $description = $listing->formattedSalePrice().' · '.\Illuminate\Support\Str::limit(\Illuminate\Support\Str::squish($listing->description), 150);
@endphp
@section('title', __(':title — купить изделие ручной работы', ['title' => $listing->title]).' | Handmade Work')
@section('description', $description)

@push('metadata')
    @if($listing->hidden_at)
        <meta name="robots" content="noindex">
    @endif
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
<a class="text-link back" href="{{ route('catalog') }}#catalog">{{ __('К каталогу') }}</a>
<article class="detail">
    <img src="{{ route('listings.photo', $listing) }}" alt="{{ $listing->title }}">
    <div class="detail-copy">
        <p class="category"><a class="text-link" href="{{ route('catalog', ['category' => $listing->category]) }}">{{ $categoryTitle }}</a></p>
        <h1>{{ $listing->title }}</h1>
        <p class="price">@include('listing-price')</p>
        <dl class="fulfillment">
            @if($listing->city)
                <div><dt>{{ __('Город') }}</dt><dd>{{ $listing->city }}</dd></div>
            @endif
            <div>
                <dt>{{ __('Получение') }}</dt>
                <dd>{{ __(\App\Models\Listing::FULFILLMENT_METHODS[$listing->fulfillment_method] ?? 'По договорённости') }}</dd>
            </div>
        </dl>
        <p class="description">{{ $listing->description }}</p>
        <a class="button" href="mailto:{{ str_replace("%40", "@", rawurlencode($listing->contact_email)) }}?subject={{ rawurlencode($listing->title) }}">{{ __('Написать продавцу') }}</a>
        @if($listing->contact_phone)
            <p><a class="button outline" href="tel:{{ $listing->contact_phone }}">{{ __('Позвонить :phone', ['phone' => $listing->contact_phone]) }}</a></p>
        @endif
        <p class="contact">{{ __('Договоритесь с мастером о получении и оплате.') }}</p>
        <p class="contact">{{ $listing->contact_email }}</p>
        @if($listing->hidden_at)
            <p class="notice">{{ __('Объявление скрыто') }}
                @if($listing->moderation_reason) · {{ $listing->moderation_reason }} @endif
            </p>
        @else
            <div class="listing-actions" id="interactions">
                @auth
                    <form action="{{ route($liked ? 'listings.unlike' : 'listings.like', $listing) }}" method="post">
                        @csrf
                        @method($liked ? 'DELETE' : 'PUT')
                        <button type="submit" class="button outline small" aria-pressed="{{ $liked ? 'true' : 'false' }}">
                            <span aria-hidden="true">{{ $liked ? '♥' : '♡' }}</span>
                            {{ __('Нравится') }} · {{ $listing->likes_count }}
                        </button>
                    </form>
                @else
                    <a class="button outline small" href="{{ route('listings.participate', $listing) }}" aria-label="{{ __('Войти, чтобы поставить лайк') }}">
                        <span aria-hidden="true">♡</span> {{ __('Нравится') }} · {{ $listing->likes_count }}
                    </a>
                @endauth
                <a class="text-link" href="#comments">{{ __('Комментарии') }} · {{ $comments->total() }}</a>
                @include('listing-share')
            </div>
        @endif
        @can('update', $listing)
            <p><a class="button outline" href="{{ route('listings.edit', $listing) }}">{{ __('Редактировать') }}</a></p>
        @endcan
        @can('delete', $listing)
            <form class="remove" action="{{ route('listings.destroy', $listing) }}" method="post" data-confirm="{{ __('Снять изделие с продажи?') }}" onsubmit="return confirm(this.dataset.confirm)">
                @csrf
                @method('DELETE')
                <button class="button outline" type="submit">{{ __('Снять с продажи') }}</button>
            </form>
        @endcan
    </div>
</article>
@unless($listing->hidden_at)
    @include('listing-comments')
@endunless
@endsection
