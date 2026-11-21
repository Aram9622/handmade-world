<a href="{{ route('catalog') }}">{{ __('Главная') }}</a>
<a href="{{ route('catalog') }}#category-heading">{{ __('Категории каталога') }}</a>
<a href="{{ route('catalog') }}#catalog">{{ __('Каталог') }}</a>
@auth
    <a href="{{ route('subscriptions.index') }}">{{ __('Мои подписки') }}</a>
@endauth
