@inject('descriptionFormatter', 'App\Services\ListingDescription')
@php
    $plain = old('description', $listing?->description);
    $html = old('description_html', $listing?->description_html);
    $rows = array_values(array_filter((array) old('characteristics', $listing?->characteristics ?? []), 'is_array'));
    $blank = ['label' => '', 'value' => ''];
@endphp
<fieldset class="listing-edit-section" data-description-editor>
    <legend id="listing-description-label">{{ __('Описание') }}</legend>
    <textarea name="description" rows="5" maxlength="2000" aria-labelledby="listing-description-label"
        placeholder="{{ __('Расскажите о материалах, размере и особенностях изделия') }}" required>{{ is_string($plain) ? $plain : '' }}</textarea>
    <div data-rich-controls hidden>
        <div class="listing-editor-toolbar" role="group" aria-label="{{ __('Форматирование описания') }}">
            <button type="button" data-command="bold"><strong>{{ __('Жирный') }}</strong></button>
            <button type="button" data-command="italic"><em>{{ __('Курсив') }}</em></button>
            <button type="button" data-command="formatBlock" data-value="h3">{{ __('Подзаголовок') }}</button>
            <button type="button" data-command="formatBlock" data-value="p">{{ __('Абзац') }}</button>
            <button type="button" data-command="insertUnorderedList">{{ __('Список') }}</button>
        </div>
        <div class="listing-rich-editor" data-rich-editor contenteditable="true" role="textbox" aria-multiline="true"
            aria-labelledby="listing-description-label">{!! is_string($html) && $html !== '' ? $descriptionFormatter->clean(mb_substr($html, 0, 12000)) : nl2br(e(is_string($plain) ? $plain : '')) !!}</div>
        <input type="hidden" name="description_html" disabled>
    </div>
</fieldset>

<fieldset class="listing-edit-section" data-characteristics data-limit="{{ \App\Models\Listing::MAX_CHARACTERISTICS }}">
    <legend>{{ __('Характеристики') }}</legend>
    <small>{{ __('Например, материал, размер или цвет. Оставьте пустые строки, если характеристик нет.') }}</small>
    <div data-characteristic-rows>
        @foreach($rows ?: [$blank] as $index => $row)
            <div class="listing-characteristic-row" data-characteristic-row>
                <label>{{ __('Название характеристики') }}
                    <input name="characteristics[{{ $index }}][label]" value="{{ is_string($row['label'] ?? null) ? $row['label'] : '' }}" maxlength="80" placeholder="{{ __('Материал') }}">
                </label>
                <label>{{ __('Значение') }}
                    <input name="characteristics[{{ $index }}][value]" value="{{ is_string($row['value'] ?? null) ? $row['value'] : '' }}" maxlength="180" placeholder="{{ __('Лён') }}">
                </label>
                <button type="button" class="button outline small" data-remove-characteristic hidden>{{ __('Убрать строку') }}</button>
            </div>
        @endforeach
    </div>
    <template data-characteristic-template>
        <div class="listing-characteristic-row" data-characteristic-row>
            <label>{{ __('Название характеристики') }}<input data-field="label" maxlength="80" placeholder="{{ __('Материал') }}"></label>
            <label>{{ __('Значение') }}<input data-field="value" maxlength="180" placeholder="{{ __('Лён') }}"></label>
            <button type="button" class="button outline small" data-remove-characteristic>{{ __('Убрать строку') }}</button>
        </div>
    </template>
    <button type="button" class="button outline small listing-add-characteristic" data-add-characteristic hidden>{{ __('Добавить характеристику') }}</button>
</fieldset>
