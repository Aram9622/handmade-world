@extends('layout')
@section('title', ($listing ? __('Редактировать изделие') : __('Продать изделие')).' — Handmade Work')

@push('metadata')
    <meta name="robots" content="noindex, follow">
@endpush

@section('content')
<section class="panel">
    <h1>{{ $listing ? __('Редактировать изделие') : __('Продать изделие') }}</h1>
    @include('errors')
    <form class="stack" action="{{ $listing ? route('listings.update', $listing) : route('listings.store') }}" method="post" enctype="multipart/form-data">
        @csrf
        @if($listing)
            @method('PUT')
        @endif
        <label>{{ __('Название') }}<input name="title" maxlength="100" value="{{ old('title', $listing?->title) }}" required></label>
        <label>{{ __('Категория') }}<select name="category" required><option value="">{{ __('Выберите категорию') }}</option>
        @foreach(\App\Models\Listing::CATEGORIES as $key => $name)
            <option value="{{ $key }}" @selected(old('category', $listing?->category) === $key)>{{ $categories[$key]['title'] }}</option>
        @endforeach
        </select></label>
        <label>{{ __('Описание') }}<textarea name="description" rows="5" maxlength="2000" placeholder="{{ __('Расскажите о материалах, размере и особенностях изделия') }}" required>{{ old('description', $listing?->description) }}</textarea></label>
        <label>{{ __('Цена в ₽') }}<input type="number" name="price" min="1" max="10000000" value="{{ old('price', $listing?->price ?? $defaultPrice ?? null) }}" required></label>
        <label>{{ __('Скидка, %') }}
            <small>{{ __('От 0 до 99. Оставьте поле пустым, чтобы убрать скидку.') }}</small>
            <input type="number" name="discount_percent" min="0" max="99" step="1" value="{{ old('discount_percent', $listing?->discount_percent) }}">
        </label>
        @if($listing)
            <img class="edit-photo" src="{{ route('listings.photo', $listing) }}" alt="{{ __('Текущая фотография изделия') }}">
        @endif
        <label>{{ $listing ? __('Заменить фотографию') : __('Фотография') }}
            <small>{{ __('JPG, PNG или WebP, до 5 МБ') }}</small>
            <input type="file" name="photo" accept="image/jpeg,image/png,image/webp" @required(!$listing)>
        </label>
        <label>{{ __('Город') }}
            <small>{{ __('Укажите город, если предлагаете самовывоз.') }}</small>
            <input name="city" maxlength="100" autocomplete="address-level2" value="{{ old('city', $listing?->city) }}">
        </label>
        <label>{{ __('Способ получения') }}
            <select name="fulfillment_method">
                <option value="">{{ __('По договорённости') }}</option>
                @foreach(\App\Models\Listing::FULFILLMENT_METHODS as $key => $label)
                    <option value="{{ $key }}" @selected(old('fulfillment_method', $listing?->fulfillment_method) === $key)>{{ __($label) }}</option>
                @endforeach
            </select>
        </label>
        <label>{{ __('Почта для покупателей') }} <small>{{ __('Адрес будет виден в объявлении.') }}</small><input type="email" name="contact_email" maxlength="254" value="{{ old('contact_email', $listing?->contact_email) }}" required></label>
        <label>{{ __('Телефон для покупателей') }}
            <small>{{ __('Необязательно. Номер с кодом страны будет виден всем в объявлении.') }}</small>
            <input type="tel" name="contact_phone" autocomplete="tel" maxlength="30" placeholder="+7 900 123-45-67" value="{{ old('contact_phone', $listing?->contact_phone) }}">
        </label>
        <button class="button" type="submit">{{ $listing ? __('Сохранить изменения') : __('Опубликовать объявление') }}</button>
    </form>
</section>
@endsection
