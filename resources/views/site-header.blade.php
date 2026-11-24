<header class="header">
    <div class="site-bar">
        <a class="brand" href="{{ route('catalog') }}"><span class="mark" aria-hidden="true">✿</span> Handmade <em>Work</em></a>

        <nav class="site-links" aria-label="{{ __('Основная навигация') }}">
            @include('site-links')
        </nav>

        <div class="site-tools">
            @include('language-menu')
            @foreach($storefrontMenu->get('tools', []) as $key => $item)
                <a class="{{ $key === 'sell' ? 'button small site-sell' : 'site-login text-link' }}" href="{{ $item['href'] }}">{{ $item['text'] }}</a>
            @endforeach
            @if(auth()->check() || $storefrontMenu->hasAny(['main', 'tools', 'account']))
                <details class="header-menu">
                    <summary class="header-control" aria-label="{{ __('Меню') }}"><span class="menu-lines" aria-hidden="true"></span></summary>
                    <nav class="header-popover storefront-popover" aria-label="{{ __('Меню') }}">
                        @include('site-links')
                        @foreach($storefrontMenu->get('account', []) as $item)
                            <a href="{{ $item['href'] }}">{{ $item['text'] }}</a>
                        @endforeach
                        @auth
                            <form action="{{ route('logout') }}" method="post">
                                @csrf
                                <button type="submit">{{ __('Выйти') }}</button>
                            </form>
                        @endauth
                        @foreach($storefrontMenu->get('tools', []) as $item)
                            <a href="{{ $item['href'] }}">{{ $item['text'] }}</a>
                        @endforeach
                    </nav>
                </details>
            @endif
        </div>
    </div>
</header>
