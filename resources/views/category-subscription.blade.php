<div id="category-subscription">
    @auth
        @php($subscribed = in_array($category, $subscribedCategories, true))
        <form method="post" action="{{ route($subscribed ? 'subscriptions.destroy' : 'subscriptions.store', ['category' => $category]) }}">
            @csrf
            @method($subscribed ? 'DELETE' : 'PUT')
            <button class="button outline" type="submit">{{ $subscribed ? __('Отписаться от категории') : __('Подписаться на категорию') }}</button>
        </form>
    @else
        <a class="button outline" href="{{ route('subscriptions.participate', ['category' => $category]) }}">{{ __('Войти, чтобы подписаться') }}</a>
    @endauth
</div>
