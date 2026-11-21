@extends('layout')
@section('title', __('Админка').' — Handmade Work')

@push('metadata')
    <meta name="robots" content="noindex">
@endpush

@section('content')
<section class="catalog admin-page">
    <h1 class="catalog-title">{{ __('Управление площадкой') }}</h1>
    <nav class="category-nav" aria-label="{{ __('Админка') }}">
        @foreach(['listings' => 'Объявления', 'comments' => 'Комментарии', 'users' => 'Пользователи'] as $tab => $label)
            <a class="text-link" href="{{ route('admin.index', ['section' => $tab]) }}" @if($tab === $section) aria-current="page" @endif>{{ __($label) }}</a>
        @endforeach
    </nav>

    <form class="admin-search" action="{{ route('admin.index') }}" method="get">
        <input type="hidden" name="section" value="{{ $section }}">
        <label>{{ __('Поиск') }}<input name="q" value="{{ $search }}" maxlength="100"></label>
        <button class="button" type="submit">{{ __('Найти') }}</button>
    </form>
    @include('errors')

    <ul class="admin-records">
        @forelse($records as $record)
            <li class="admin-record">
                @if($section === 'users')
                    @include('admin.user', ['user' => $record])
                @elseif($section === 'listings')
                    @include('admin.listing', ['listing' => $record])
                @else
                    @include('admin.comment', ['comment' => $record])
                @endif
            </li>
        @empty
            <li>{{ __('Ничего не найдено.') }}</li>
        @endforelse
    </ul>
    @include('listing-pagination', ['listings' => $records])
</section>
@endsection
