<!doctype html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="@yield('description', __('Каталог изделий ручной работы от независимых мастеров.'))">
    @stack('metadata')
    <title>@yield('title', __('Handmade Work — изделия ручной работы'))</title>
    <link rel="stylesheet" href="{{ asset('market.css') }}">
    <script src="{{ asset('market-motion.js') }}" defer></script>
</head>
<body>
<header class="header">
    <div class="wrap nav">
        <a class="brand" href="{{ route('catalog') }}"><span class="mark" aria-hidden="true">✿</span> Handmade <em>Work</em></a>
        <nav aria-label="{{ __('Основная навигация') }}">
            <a class="nav-link" href="{{ route('catalog') }}#catalog">{{ __('Каталог') }}</a>
            @auth
                <a class="nav-link" href="{{ route('listings.mine') }}">{{ __('Мои изделия') }}</a>
                <form action="{{ route('logout') }}" method="post">@csrf <button class="nav-link" type="submit">{{ __('Выйти') }}</button></form>
            @else
                <a class="nav-link" href="{{ route('login') }}">{{ __('Войти') }}</a>
            @endauth
            <a class="button small" href="{{ route('listings.create') }}">＋ <span>{{ __('Продать изделие') }}</span></a>
        </nav>
        <nav class="locale-switcher" aria-label="{{ __('Язык') }}">
            @foreach(\App\Http\Middleware\SetLocale::LANGUAGES as $code => $label)
                <a href="{{ request()->fullUrlWithQuery(['lang' => $code]) }}" lang="{{ $code }}" @if(app()->isLocale($code)) aria-current="true" @endif>{{ $label }}</a>
            @endforeach
        </nav>
    </div>
</header>
<main class="wrap">
    @if(session('status'))
        <p class="notice" role="status">{{ session('status') }}</p>
    @endif
    @yield('content')
</main>
<footer class="footer"><div class="wrap"><strong>Handmade Work</strong><span>{{ __('Площадка для мастеров и ценителей ручной работы') }}</span></div></footer>
</body>
</html>
