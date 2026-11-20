@php
    $url = route('listings.show', $listing);
    $message = 'Продаётся: '.$listing->title.' · '.$listing->formattedSalePrice();
@endphp

<details class="share" data-listing-share data-message="{{ $message }}">
    <summary class="text-link">Поделиться объявлением</summary>
    <div class="share-actions">
        <button type="button" class="button outline small" data-native-share hidden>Выбрать приложение</button>
        <a class="button outline small" href="https://t.me/share/url?{{ http_build_query(['url' => $url, 'text' => $message], encoding_type: PHP_QUERY_RFC3986) }}" target="_blank" rel="noopener noreferrer">Telegram</a>
        <a class="button outline small" href="https://vk.com/share.php?{{ http_build_query(['url' => $url, 'title' => $message], encoding_type: PHP_QUERY_RFC3986) }}" target="_blank" rel="noopener noreferrer">ВКонтакте</a>
        <button type="button" class="button outline small" data-copy-link hidden>Скопировать ссылку</button>
    </div>
    <label>Ссылка на объявление
        <input type="url" name="share_url" value="{{ $url }}" readonly>
    </label>
    <p class="share-status" role="status"></p>
</details>
