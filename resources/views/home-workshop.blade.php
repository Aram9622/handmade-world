<section class="home-workshop" aria-labelledby="workshop-heading">
    <div class="wrap home-workshop-inner">
        <img src="{{ asset('images/workshop.webp') }}" alt="" loading="lazy">
        <div class="home-workshop-copy">
            <h2 id="workshop-heading">{{ __('От мастерской до вашего дома') }}</h2>
            <p>{{ __('Выберите изделие и свяжитесь с мастером по контактам в объявлении. Об оплате и получении вы договариваетесь напрямую.') }}</p>
            <a class="button" href="#catalog">{{ __('Смотреть весь каталог') }}</a>
            <div class="home-workshop-sell">
                <h3>{{ __('Создаёте своими руками?') }}</h3>
                <p>{{ __('Добавьте фото, укажите цену и оставьте контакты для покупателей.') }}</p>
                <a class="button outline" href="{{ route('listings.create') }}">{{ __('Продать изделие') }}</a>
            </div>
        </div>
    </div>
</section>
