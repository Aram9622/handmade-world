<details class="header-menu">
    <summary class="header-control language-control" aria-label="{{ __('Язык') }}">{{ strtoupper(app()->getLocale()) }}</summary>
    <nav class="header-popover locale-switcher" aria-label="{{ __('Язык') }}">
        @foreach(\App\Http\Middleware\SetLocale::LANGUAGES as $code => $label)
            <a href="{{ request()->fullUrlWithQuery(['lang' => $code]) }}" lang="{{ $code }}" @if(app()->isLocale($code)) aria-current="true" @endif>{{ $label }}</a>
        @endforeach
    </nav>
</details>
