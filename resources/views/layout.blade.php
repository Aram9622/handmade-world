<!doctype html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Каталог изделий ручной работы от независимых мастеров.">
    <title>@yield('title', 'Handmade Work — изделия ручной работы')</title>
    <link rel="stylesheet" href="{{ asset('market.css') }}">
</head>
<body>
<header class="header">
    <div class="wrap nav">
        <a class="brand" href="{{ route('catalog') }}"><span class="mark" aria-hidden="true">✿</span> Handmade <em>Work</em></a>
        <nav aria-label="Основная навигация">
            <a class="nav-link" href="{{ route('catalog') }}#catalog">Каталог</a>
            @auth
                <form action="{{ route('logout') }}" method="post">@csrf <button class="nav-link" type="submit">Выйти</button></form>
            @else
                <a class="nav-link" href="{{ route('login') }}">Войти</a>
            @endauth
            <a class="button small" href="{{ route('listings.create') }}">＋ <span>Продать изделие</span></a>
        </nav>
    </div>
</header>
<main class="wrap">
    @if(session('status'))
        <p class="notice" role="status">{{ session('status') }}</p>
    @endif
    @yield('content')
</main>
<footer class="footer"><div class="wrap"><strong>Handmade Work</strong><span>Площадка для мастеров и ценителей ручной работы</span></div></footer>
</body>
</html>
