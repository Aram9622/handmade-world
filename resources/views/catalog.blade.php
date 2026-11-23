@extends('layout')

@php
    $categoryName = $category !== '' ? $editorial[$category]['title'] : null;
    $catalogTitle = $subscriptionFeed ? __('Мои подписки') : match ($category) {
        '' => __('Изделия ручной работы'),
        'other' => __('Другие изделия ручной работы'),
        default => __(':category ручной работы', ['category' => $categoryName]),
    };
    $page = $listings->currentPage();
    $showEditorial = !$subscriptionFeed && $search === '' && $sort === 'new' && $page === 1;
    $canonicalParameters = array_filter(['category' => $category, 'page' => $page > 1 ? $page : null]);
@endphp
@section('title', $catalogTitle.($page > 1 ? (' — '.__('страница :page', ['page' => $page])) : '').' | Handmade Work')
@section('description', __(':catalog от независимых мастеров. Смотрите фотографии, сравнивайте цены и связывайтесь с продавцами напрямую.', ['catalog' => $catalogTitle]))

@if($categoryTheme)
    @section('category-theme', '--category-background:'.$categoryTheme['background'].';--category-accent:'.$categoryTheme['accent'].';--category-on-background:'.$categoryTheme['on_background'].';--category-on-accent:'.$categoryTheme['on_accent'])
@endif

@push('metadata')
    @if($subscriptionFeed || $search !== '' || $sort !== 'new' || ($page > 1 && $listings->isEmpty()))
        <meta name="robots" content="noindex, follow">
    @else
        <link rel="canonical" href="{{ route('catalog', $canonicalParameters) }}">
    @endif
@endpush

@if($showEditorial)
    @section('cover')
        @if($categoryName)
            @include('category-cover', ['story' => $editorial[$category]])
        @else
            @include('home-cover')
        @endif
    @endsection
@endif

@section('content')
@if($showEditorial && $categoryName)
    @include('category-editorial', ['story' => $editorial[$category]])
@elseif($showEditorial)
    @include('catalog-editorial')
@endif

<section id="catalog" class="catalog">
    @if(!$showEditorial)
        <h1 class="catalog-title">{{ $subscriptionFeed ? $catalogTitle : ($categoryName ?? __('Каталог изделий')) }}</h1>
    @else
        <h2>{{ $categoryName ? __('Объявления мастеров') : __('Каталог изделий') }}</h2>
    @endif
    @if($subscriptionFeed)
        <nav class="category-nav" aria-label="{{ __('Мои подписки') }}">
            <a class="text-link" href="{{ route('subscriptions.index') }}" @if($category === '') aria-current="page" @endif>{{ __('Все подписки') }}</a>
            @foreach($subscribedCategories as $key)
                <a class="text-link" href="{{ route('catalog', ['category' => $key]) }}#category-subscription">{{ $editorial[$key]['title'] }}</a>
            @endforeach
            <a class="text-link" href="{{ route('catalog') }}#category-heading">{{ __('Все категории') }}</a>
        </nav>
    @elseif(!$showEditorial && $categoryName)
        @include('category-subscription')
    @endif
    <form class="filters" action="{{ route($subscriptionFeed ? 'subscriptions.index' : 'catalog') }}#catalog" method="get">
        <label>{{ __('Поиск') }}<input name="q" value="{{ $search }}" placeholder="{{ __('Например, керамическая чашка') }}" maxlength="100"></label>
        <label>{{ __('Категория') }}
            <select name="category">
                <option value="">{{ __('Все категории') }}</option>
                @foreach(array_diff_key($editorial, ['home' => true]) as $key => $name)
                    @if($subscriptionFeed ? in_array($key, $subscribedCategories, true) : ($key === $category || isset($populatedCategories[$key])))
                        <option value="{{ $key }}" @selected($key === $category)>{{ $editorial[$key]['title'] }}</option>
                    @endif
                @endforeach
            </select>
        </label>
        <label>{{ __('Сортировка') }}
            <select name="sort">
                <option value="new" @selected($sort === 'new')>{{ __('Сначала новые') }}</option>
                @foreach(array_keys(\App\Models\Listing::CURRENCIES) as $currency)
                    @php($suffix = $currency === 'RUB' ? '' : '-'.strtolower($currency))
                    <optgroup label="{{ $currency }}">
                        <option value="low{{ $suffix }}" @selected($sort === 'low'.$suffix)>{{ __('Сначала дешевле') }}</option>
                        <option value="high{{ $suffix }}" @selected($sort === 'high'.$suffix)>{{ __('Сначала дороже') }}</option>
                    </optgroup>
                @endforeach
            </select>
        </label>
        <button class="button" type="submit">{{ __('Найти') }}</button>
    </form>
    @include('errors')
    <div id="catalog-results">
        <h3 class="results-heading">{{ $showFeed ? __('Новые изделия') : __('Результаты поиска') }}</h3>
    </div>
    <div class="grid">
        @forelse($listings as $listing)
            @include('listing-card', [
                'cardCategory' => $editorial[$listing->category]['title'],
                'heading' => 'h4',
                'showPublishedDate' => $showFeed,
            ])
        @empty
            <div class="empty">
                @if($subscriptionFeed && !$subscribedCategories)
                    <p>{{ __('Подпишитесь на интересные категории, чтобы видеть новые изделия здесь.') }}</p>
                    <a class="button outline" href="{{ route('catalog') }}#category-heading">{{ __('Все категории') }}</a>
                @elseif($subscriptionFeed)
                    <p>{{ __('В подписках пока нет изделий по этим фильтрам.') }}</p>
                    <a class="button outline" href="{{ route('subscriptions.index') }}">{{ __('Сбросить фильтры') }}</a>
                @elseif($category && $search === '')
                    <p>{{ __('В этом разделе пока нет объявлений.') }}</p>
                    <a class="button outline" href="{{ route('catalog') }}#catalog">{{ __('Смотреть весь каталог') }}</a>
                @elseif($search)
                    <p>{{ __('По этим фильтрам ничего не найдено.') }}</p>
                    <a class="button outline" href="{{ route('catalog') }}#catalog">{{ __('Сбросить фильтры') }}</a>
                @else
                    <p>{{ __('В каталоге пока нет объявлений.') }}</p>
                    <a class="button outline" href="{{ route('listings.create') }}">{{ __('Добавить первое изделие') }}</a>
                @endif
            </div>
        @endforelse
    </div>
    @include('listing-pagination', ['anchor' => '#catalog-results'])
</section>
@endsection

@if($showEditorial && !$categoryName)
    @section('after-content')
        @include('home-workshop')
    @endsection
@endif
