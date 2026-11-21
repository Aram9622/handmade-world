<!doctype html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="@yield('description', __('Каталог изделий ручной работы от независимых мастеров.'))">
    @stack('metadata')
    <title>@yield('title', __('Handmade Work — изделия ручной работы'))</title>
    <link rel="stylesheet" href="{{ asset('market.css') }}">
    @stack('styles')
    <script src="{{ asset('market-motion.js') }}" defer></script>
</head>
<body>
@section('body')
@include('site-header')
<main>
    @if(session('status'))
        <div class="wrap">
            <p class="notice" role="status">{{ session('status') }}</p>
        </div>
    @endif
    @yield('cover')
    <div class="wrap">
        @yield('content')
    </div>
    @yield('after-content')
</main>
<footer class="footer"><div class="wrap"><strong>Handmade Work</strong><span>{{ __('Площадка для мастеров и ценителей ручной работы') }}</span></div></footer>
@show
</body>
</html>
