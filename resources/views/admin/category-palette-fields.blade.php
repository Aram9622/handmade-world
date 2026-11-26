<input type="hidden" name="custom_palette" value="0">
<label class="admin-palette-toggle">
    <input type="checkbox" name="custom_palette" value="1" @checked(old('custom_palette', isset($palette)))>
    {{ __('Использовать свою гамму') }}
</label>
<div class="admin-menu-fields">
    <label>{{ __('Фон обложки') }}
        <input type="color" name="palette[background]" value="{{ old('palette.background', $palette['background'] ?? '#f1e1ce') }}">
    </label>
    <label>{{ __('Цвет кнопок и акцентов') }}
        <input type="color" name="palette[accent]" value="{{ old('palette.accent', $palette['accent'] ?? '#8b3d2c') }}">
    </label>
</div>
