@extends('layout')

@push('metadata')
    <meta name="robots" content="noindex">
@endpush

@push('styles')
    <link rel="stylesheet" href="{{ asset('admin.css') }}">
@endpush

@section('body')
<div class="admin-shell">
    @include('admin.sidebar', ['sections' => \App\Http\Controllers\AdminController::SECTIONS])

    <main class="admin-main">
        <header class="admin-header">
            <h1>@yield('admin-heading')</h1>
            <div class="admin-header-tools">
                @include('language-menu')
                <details class="header-menu">
                    <summary class="header-control admin-account">
                        <span class="admin-avatar" aria-hidden="true">{{ mb_strtoupper(mb_substr(auth()->user()->name, 0, 1)) }}</span>
                        <span>{{ auth()->user()->name }}</span>
                    </summary>
                    <div class="header-popover">
                        <form action="{{ route('logout') }}" method="post">@csrf <button type="submit">{{ __('Выйти') }}</button></form>
                    </div>
                </details>
            </div>
        </header>

        @if(session('status'))
            <p class="notice" role="status">{{ session('status') }}</p>
        @endif

        @yield('admin-content')
    </main>
</div>
@endsection
