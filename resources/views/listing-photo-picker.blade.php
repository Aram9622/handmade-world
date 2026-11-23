<fieldset class="photo-picker" data-photo-picker
    data-preview-alt="{{ __('Предпросмотр фотографии') }}"
    data-preview-error="{{ __('Не удалось открыть фото. Выберите JPG, PNG или WebP до 5 МБ.') }}">
    <legend>{{ __('Фотография') }}</legend>
    <div class="photo-preview">
        <img data-photo-preview @if($listing) src="{{ route('listings.photo', $listing) }}" @else hidden @endif
            alt="{{ __('Текущая фотография изделия') }}">
        <div class="photo-placeholder" data-photo-placeholder @if($listing) hidden @endif aria-hidden="true">+</div>
    </div>
    @php($chooseLabel = $listing ? __('Заменить фотографию') : __('Выбрать фотографию'))
    <div class="photo-picker-actions">
        <label class="photo-picker-control">
            <span class="button small">{{ $chooseLabel }}</span>
            <input type="file" name="photo" accept="image/jpeg,image/png,image/webp" aria-label="{{ $chooseLabel }}" aria-describedby="photo-formats photo-status" @required(!$listing)>
        </label>
        <button class="button outline small" data-photo-clear type="button" hidden>{{ __('Отменить выбор') }}</button>
    </div>
    <small id="photo-formats">{{ __('JPG, PNG или WebP, до 5 МБ') }}</small>
    <small id="photo-status" role="status"></small>
</fieldset>
