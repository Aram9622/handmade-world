@extends('layout')
@section('title', __('Админка').' — Handmade Work')

@push('metadata')
    <meta name="robots" content="noindex">
    <link rel="stylesheet" href="{{ asset('admin.css') }}">
@endpush

@section('body')
@php
    $sections = ['listings' => 'Объявления', 'comments' => 'Комментарии', 'users' => 'Пользователи'];
@endphp
<div class="admin-shell">
    @include('admin.sidebar')

    <main class="admin-main">
        <header class="admin-header">
            <h1>{{ __('Управление площадкой') }}</h1>
            <div class="admin-header-tools">
                @include('language-menu')
                <details class="header-menu">
                    <summary class="header-control admin-account">{{ auth()->user()->name }}</summary>
                    <div class="header-popover">
                        <form action="{{ route('logout') }}" method="post">@csrf <button type="submit">{{ __('Выйти') }}</button></form>
                    </div>
                </details>
            </div>
        </header>

        <dl class="admin-totals">
            @foreach($sections as $tab => $label)
                <div><dt>{{ __($label) }}</dt><dd>{{ $totals[$tab] }}</dd></div>
            @endforeach
        </dl>

        @if(session('status'))
            <p class="notice" role="status">{{ session('status') }}</p>
        @endif

        <section class="admin-panel" aria-labelledby="admin-section-title">
            <header class="admin-panel-header">
                <div>
                    <h2 id="admin-section-title">{{ __($sections[$section]) }}</h2>
                    <p>{{ __('Найдено: :count', ['count' => $records->total()]) }}</p>
                </div>
                <form class="admin-search" action="{{ route('admin.index') }}" method="get">
                    <input type="hidden" name="section" value="{{ $section }}">
                    <input name="q" value="{{ $search }}" maxlength="100" aria-label="{{ __('Поиск') }}" placeholder="{{ __('Поиск') }}">
                    <button class="button small" type="submit">{{ __('Найти') }}</button>
                </form>
            </header>
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
                    <li class="admin-empty">{{ __('Ничего не найдено.') }}</li>
                @endforelse
            </ul>

            @include('listing-pagination', ['listings' => $records])
        </section>
    </main>
</div>
@endsection
