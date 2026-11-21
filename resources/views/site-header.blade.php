<header class="header">
    <div class="site-bar">
        <a class="brand" href="{{ route('catalog') }}"><span class="mark" aria-hidden="true">✿</span> Handmade <em>Work</em></a>

        <nav class="site-links" aria-label="{{ __('Основная навигация') }}">
            @include('site-links')
        </nav>

        <div class="site-tools">
            @include('language-menu')
            @guest
                <a class="site-login text-link" href="{{ route('login') }}">{{ __('Войти') }}</a>
            @endguest
            <a class="button small site-sell" href="{{ route('listings.create') }}">{{ __('Продать изделие') }}</a>
            <details class="header-menu">
                <summary class="header-control" aria-label="{{ __('Меню') }}"><span class="menu-lines" aria-hidden="true"></span></summary>
                <nav class="header-popover" aria-label="{{ __('Меню') }}">
                    <div class="mobile-site-links">@include('site-links')</div>
                    @auth
                        <a href="{{ route('listings.mine') }}">{{ __('Мои изделия') }}</a>
                        @can('manage-marketplace')
                            <a href="{{ route('admin.index') }}">{{ __('Админка') }}</a>
                        @endcan
                        <form action="{{ route('logout') }}" method="post">
                            @csrf
                            <button type="submit">{{ __('Выйти') }}</button>
                        </form>
                    @else
                        <a class="mobile-login" href="{{ route('login') }}">{{ __('Войти') }}</a>
                    @endauth
                    <a class="mobile-sell" href="{{ route('listings.create') }}">{{ __('Продать изделие') }}</a>
                </nav>
            </details>
        </div>
    </div>
</header>
