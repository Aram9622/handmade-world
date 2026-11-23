@extends(!empty($adminEdit) ? 'admin.layout' : 'layout')
@section('admin-heading', __('Редактировать изделие'))
@section('title', ($listing ? __('Редактировать изделие') : __('Продать изделие')).' — Handmade Work')

@push('metadata')
    <meta name="robots" content="noindex, follow">
    <script src="{{ asset('listing-photo-picker.js') }}" defer></script>
    <script src="{{ asset('listing-editor.js') }}" defer></script>
@endpush

@section(!empty($adminEdit) ? 'admin-content' : 'content')
<section class="{{ !empty($adminEdit) ? 'admin-panel' : 'panel listing-editor-panel' }}">
    @if(!empty($adminEdit))
        <a class="text-link" href="{{ $listing->publicUrl() }}">{{ __('Открыть на витрине') }}</a>
    @else
        <h1>{{ $listing ? __('Редактировать изделие') : __('Продать изделие') }}</h1>
    @endif
    @include('errors')
    <form class="stack" action="{{ $listing ? route(!empty($adminEdit) ? 'admin.listings.save' : 'listings.update', $listing) : route('listings.store') }}" method="post" enctype="multipart/form-data">
        @csrf
        @if($listing)
            @method('PUT')
        @endif
        <label>{{ __('Название') }}<input name="title" maxlength="100" value="{{ old('title', $listing?->title) }}" required></label>
        <label>{{ __('Категория') }}<select name="category" required><option value="">{{ __('Выберите категорию') }}</option>
        @foreach(array_diff_key($categories, ['home' => true]) as $key => $name)
            <option value="{{ $key }}" @selected(old('category', $listing?->category) === $key)>{{ $categories[$key]['title'] }}</option>
        @endforeach
        </select></label>
        @include('listing-detail-fields')
        <fieldset class="listing-edit-section">
            <legend>{{ __('Цены') }}</legend>
            <small>{{ __('Укажите цену хотя бы в одной валюте.') }}</small>
            <div class="listing-price-fields">
                @foreach(\App\Models\Listing::CURRENCIES as $currency => $definition)
                    <label>{{ $currency }} · {{ $definition['symbol'] }}
                        <input type="number" name="{{ $definition['column'] }}" min="1" max="10000000"
                            value="{{ old($definition['column'], $listing?->{$definition['column']}) }}">
                    </label>
                @endforeach
            </div>
        </fieldset>
        <label>{{ __('Скидка, %') }}
            <small>{{ __('От 0 до 99. Оставьте поле пустым, чтобы убрать скидку.') }}</small>
            <input type="number" name="discount_percent" min="0" max="99" step="1" value="{{ old('discount_percent', $listing?->discount_percent) }}">
        </label>
        @include('listing-photo-picker')
        @if($listing)
            <fieldset class="listing-edit-section">
                <legend>{{ __('Галерея изделия') }}</legend>
                @if($listing->gallery)
                    <div class="listing-gallery-edit">
                        @foreach($listing->gallery as $image => $path)
                            <label class="gallery-remove-photo" title="{{ __('Удалить фото') }}">
                                <input type="checkbox" name="remove_gallery[]" value="{{ $image }}"
                                    aria-label="{{ __('Удалить фото :number', ['number' => $loop->iteration]) }}"
                                    @checked(in_array($image, (array) old('remove_gallery', []), true))>
                                <img src="{{ route('listings.gallery-photo', [$listing, $image]) }}" alt="" loading="lazy">
                                <span class="gallery-remove-icon" aria-hidden="true">
                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M4 7h16M9 7V4h6v3M6 7l1 13h10l1-13m-7 4v5m2-5v5"/>
                                    </svg>
                                </span>
                            </label>
                        @endforeach
                    </div>
                @endif
                <label>{{ __('Добавить фото в галерею') }}
                    <small>{{ __('До :count дополнительных фото. JPG, PNG или WebP, до 5 МБ каждое.', ['count' => \App\Models\Listing::MAX_GALLERY_PHOTOS]) }}</small>
                    <input class="listing-gallery-upload" type="file" name="gallery_photos[]" accept="image/jpeg,image/png,image/webp" multiple>
                </label>
            </fieldset>
        @endif
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
