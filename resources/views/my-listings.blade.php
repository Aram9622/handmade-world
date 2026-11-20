@extends('layout')
@section('title', 'Мои изделия — Handmade Work')

@push('metadata')
    <meta name="robots" content="noindex, follow">
@endpush

@section('content')
<section class="catalog">
    <h2>Мои изделия</h2>
    <div class="grid">
        @forelse($listings as $listing)
            <article class="card">
                <a href="{{ route('listings.show', $listing) }}">
                    <img src="{{ route('listings.photo', $listing) }}" alt="" loading="lazy">
                    <div class="card-copy">
                        <h3>{{ $listing->title }}</h3>
                        <strong>@include('listing-price')</strong>
                    </div>
                </a>
                <div class="card-actions">
                    <a class="text-link" href="{{ route('listings.edit', $listing) }}" aria-label="Редактировать {{ $listing->title }}">Редактировать</a>
                </div>
            </article>
        @empty
            <div class="empty">
                <p>У вас пока нет объявлений.</p>
                <a class="button outline" href="{{ route('listings.create') }}">Добавить изделие</a>
            </div>
        @endforelse
    </div>
    @include('listing-pagination')
</section>
@endsection
