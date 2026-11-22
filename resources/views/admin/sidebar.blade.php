<aside class="admin-sidebar">
    <a class="brand admin-brand" href="{{ route('admin.index') }}"><span aria-hidden="true">✿</span> Handmade <em>Work</em></a>
    <nav class="admin-nav" aria-label="{{ __('Админка') }}">
        @foreach($sections as $tab => $label)
            <a href="{{ route('admin.index', ['section' => $tab]) }}" @if($tab === ($section ?? null)) aria-current="page" @endif>{{ __($label) }}</a>
        @endforeach
        <a href="{{ route('admin.editorial.edit') }}" @if(request()->routeIs('admin.editorial.*')) aria-current="page" @endif>{{ __('Контент') }}</a>
        <a href="{{ route('admin.menu.edit') }}" @if(request()->routeIs('admin.menu.*')) aria-current="page" @endif>{{ __('Меню') }}</a>
    </nav>
    <a class="admin-storefront" href="{{ route('catalog') }}">{{ __('Открыть витрину') }}</a>
</aside>
