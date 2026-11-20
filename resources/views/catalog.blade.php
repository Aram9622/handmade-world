@extends('layout')

@section('content')
<section class="hero">
    <div class="hero-copy">
        <h1>Сделано <em>руками.</em></h1>
        <p>Открывайте изделия независимых мастеров или выставляйте на продажу то, что создали сами.</p>
        <a class="button" href="#catalog">Смотреть каталог</a>
    </div>
    <div class="hero-photo" role="img" aria-label="Керамические изделия в мастерской"></div>
</section>

<section id="catalog" class="catalog">
    <h2>Каталог изделий</h2>
    <form class="filters" action="{{ route('catalog') }}#catalog" method="get">
        <label>Поиск<input name="q" value="{{ $search }}" placeholder="Например, керамическая чашка" maxlength="100"></label>
        <label>Категория
            <select name="category">
                <option value="">Все категории</option>
                @foreach(\App\Models\Listing::CATEGORIES as $key => $name)
                    <option value="{{ $key }}" @selected($key === $category)>{{ $name }}</option>
                @endforeach
            </select>
        </label>
        <label>Сортировка
            <select name="sort">
                <option value="new" @selected($sort === 'new')>Сначала новые</option>
                <option value="low" @selected($sort === 'low')>Сначала дешевле</option>
                <option value="high" @selected($sort === 'high')>Сначала дороже</option>
            </select>
        </label>
        <button class="button" type="submit">Найти</button>
    </form>
    @include('errors')
    <div class="grid">
        @forelse($listings as $listing)
            <article class="card">
                <a href="{{ route('listings.show', $listing) }}">
                    <img src="{{ route('listings.photo', $listing) }}" alt="{{ $listing->title }}" loading="lazy">
                    <div class="card-copy"><span>{{ \App\Models\Listing::CATEGORIES[$listing->category] }}</span><h3>{{ $listing->title }}</h3><strong>@include('listing-price')</strong>
                        @if($listing->city)
                            <p class="contact">{{ $listing->city }}</p>
                        @endif
                    </div>
                </a>
            </article>
        @empty
            <div class="empty">
                @if($search || $category)
                    <p>По этим фильтрам ничего не найдено.</p>
                    <a class="button outline" href="{{ route('catalog') }}#catalog">Сбросить фильтры</a>
                @else
                    <p>В каталоге пока нет объявлений.</p>
                    <a class="button outline" href="{{ route('listings.create') }}">Добавить первое изделие</a>
                @endif
            </div>
        @endforelse
    </div>
    @include('listing-pagination', ['anchor' => '#catalog'])
</section>
@endsection
