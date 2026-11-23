@php
    $url = $listing->publicUrl();
    $message = __('Продаётся: :title · :price', ['title' => $listing->title, 'price' => $listing->formattedSalePrice()]);
@endphp

<details class="share" data-listing-share data-message="{{ $message }}"
    data-share-error="{{ __('Выберите Telegram, ВКонтакте или скопируйте ссылку.') }}"
    data-copied="{{ __('Ссылка скопирована.') }}"
    data-copy-fallback="{{ __('Ссылка выделена. Скопируйте её вручную.') }}">
    <summary class="text-link">{{ __('Поделиться объявлением') }}</summary>
    <div class="share-actions">
        <button type="button" class="button outline small" data-native-share hidden>{{ __('Выбрать приложение') }}</button>
        <a class="button outline small" href="https://t.me/share/url?{{ http_build_query(['url' => $url, 'text' => $message], encoding_type: PHP_QUERY_RFC3986) }}" target="_blank" rel="noopener noreferrer">Telegram</a>
        <a class="button outline small" href="https://vk.com/share.php?{{ http_build_query(['url' => $url, 'title' => $message], encoding_type: PHP_QUERY_RFC3986) }}" target="_blank" rel="noopener noreferrer">{{ __('ВКонтакте') }}</a>
        <button type="button" class="button outline small" data-copy-link hidden>{{ __('Скопировать ссылку') }}</button>
    </div>
    <label>{{ __('Ссылка на объявление') }}
        <input type="url" name="share_url" value="{{ $url }}" readonly>
    </label>
    <p class="share-status" role="status"></p>
</details>
