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
    <script src="{{ asset('listing-gallery.js') }}" defer></script>
    <script src="{{ asset('listing-price-picker.js') }}" defer></script>
@endpush

@section('content')
<a class="text-link back" href="{{ route('catalog') }}#catalog">{{ __('К каталогу') }}</a>
<article class="detail">
    <div class="listing-gallery" data-listing-gallery>
        <div class="listing-gallery-stage" data-gallery-swipe>
            <a class="listing-gallery-main" href="{{ $gallery[0] }}" data-gallery-main aria-label="{{ __('Увеличить фото') }}">
                <img src="{{ $gallery[0] }}" alt="{{ $listing->title }}" fetchpriority="high">
                <span class="listing-gallery-zoom" aria-hidden="true">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M8 4H4v4m12-4h4v4M4 16v4h4m12-4v4h-4"/>
                    </svg>
                </span>
            </a>
            @if(count($gallery) > 1)
                @include('listing-gallery-controls')
            @endif
        </div>
        <dialog class="listing-gallery-dialog" data-gallery-dialog aria-label="{{ __('Галерея изделия') }}">
            <form method="dialog">
                <button class="listing-gallery-close" type="submit" aria-label="{{ __('Закрыть просмотр фото') }}" autofocus>
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true">
                        <path d="m6 6 12 12M18 6 6 18"/>
                    </svg>
                </button>
            </form>
            <img alt="{{ $listing->title }}" data-gallery-swipe>
            @if(count($gallery) > 1)
                @include('listing-gallery-controls')
            @endif
        </dialog>
        @if(count($gallery) > 1)
            <div class="listing-gallery-thumbs" role="group" aria-label="{{ __('Галерея изделия') }}">
                @foreach($gallery as $url)
                    <a href="{{ $url }}" data-gallery-thumb aria-label="{{ __('Фото :number', ['number' => $loop->iteration]) }}" @if($loop->first) aria-current="true" @endif>
                        <img src="{{ $url }}" alt="" loading="lazy">
                    </a>
                @endforeach
            </div>
        @endif
    </div>
    <div class="detail-copy">
        <p class="category"><a class="text-link" href="{{ route('catalog', ['category' => $listing->category]) }}">{{ $categoryTitle }}</a></p>
        <h1>{{ $listing->title }}</h1>
        @include('listing-price-picker')
        <dl class="fulfillment">
            @if($listing->city)
                <div><dt>{{ __('Город') }}</dt><dd>{{ $listing->city }}</dd></div>
            @endif
            <div>
                <dt>{{ __('Получение') }}</dt>
                <dd>{{ __(\App\Models\Listing::FULFILLMENT_METHODS[$listing->fulfillment_method] ?? 'По договорённости') }}</dd>
            </div>
        </dl>
        <div class="listing-contact-actions">
            <a class="button" href="mailto:{{ str_replace("%40", "@", rawurlencode($listing->contact_email)) }}?subject={{ rawurlencode($listing->title) }}">{{ __('Написать продавцу') }}</a>
            @if($listing->contact_phone)
                <a class="button outline" href="tel:{{ $listing->contact_phone }}">{{ __('Позвонить :phone', ['phone' => $listing->contact_phone]) }}</a>
            @endif
        </div>
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
    <div class="listing-information">
        <section class="listing-information-panel">
            <h2>{{ __('Описание') }}</h2>
            @if($listing->description_html)
                <div class="listing-prose">{!! $listing->description_html !!}</div>
            @else
                <p class="description">{{ $listing->description }}</p>
            @endif
        </section>
        @if($listing->characteristics)
            <section class="listing-information-panel">
                <h2>{{ __('Характеристики') }}</h2>
                <dl class="listing-specifications">
                    @foreach($listing->characteristics as $row)
                        <div><dt>{{ $row['label'] }}</dt><dd>{{ $row['value'] }}</dd></div>
                    @endforeach
                </dl>
            </section>
        @endif
    </div>
</article>
@unless($listing->hidden_at)
    @include('listing-comments')
@endunless
@endsection
