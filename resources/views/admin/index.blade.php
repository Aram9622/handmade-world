@extends('admin.layout')
@section('title', __('Админка').' — Handmade Work')
@section('admin-heading', __('Управление площадкой'))

@section('admin-content')
    <nav class="admin-overview" aria-label="{{ __('Сводка по площадке') }}">
        @foreach($sections as $tab => $label)
            <a class="admin-stat admin-stat-{{ $tab }}" href="{{ route('admin.index', ['section' => $tab]) }}" @if($tab === $section) aria-current="page" @endif>
                <span class="admin-stat-label">{{ __($label) }}</span>
                <strong>{{ number_format($totals[$tab], 0, '', ' ') }}</strong>
            </a>
        @endforeach
    </nav>

    <section class="admin-panel" aria-labelledby="admin-section-title">
        <header class="admin-panel-header">
            <div>
                <h2 id="admin-section-title">{{ __($sections[$section]) }}</h2>
                <p>{{ __('Найдено: :count', ['count' => $records->total()]) }}</p>
            </div>
            <form class="admin-search" action="{{ route('admin.index') }}" method="get">
                <input type="hidden" name="section" value="{{ $section }}">
                <input name="q" type="search" value="{{ $search }}" maxlength="100" aria-label="{{ __('Поиск') }}" placeholder="{{ __('Поиск') }}">
                <button class="button small" type="submit">{{ __('Найти') }}</button>
            </form>
        </header>
        @include('errors')

        @if($records->isEmpty())
            <p class="admin-empty">{{ __('Ничего не найдено.') }}</p>
        @else
            <div class="admin-table-scroll" role="region" aria-labelledby="admin-section-title" tabindex="0">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th scope="col">{{ __($sections[$section]) }}</th>
                            <th scope="col">{{ __('Статус') }}</th>
                            <th scope="col">{{ __('Действия') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($records as $record)
                            <tr>
                                @if($section === 'users')
                                    @include('admin.user', ['user' => $record])
                                @elseif($section === 'listings')
                                    @include('admin.listing', ['listing' => $record])
                                @else
                                    @include('admin.comment', ['comment' => $record])
                                @endif
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
        @include('listing-pagination', ['listings' => $records])
    </section>
@endsection
