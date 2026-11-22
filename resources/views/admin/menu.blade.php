@extends('admin.layout')

@section('title', __('Меню витрины').' — Handmade Work')
@section('admin-heading', __('Меню витрины'))

@section('admin-content')
@include('errors')

<form class="stack" method="post" action="{{ route('admin.menu.update') }}">
    @csrf
    @method('PUT')
    <p>{{ __('В каждой части меню пункты с меньшим номером показываются первыми.') }}</p>
    @php($groups = $items->groupBy('area', preserveKeys: true))
    @foreach($groups->except('categories')->put('categories', $groups->get('categories', collect())) as $area => $group)
        @php($heading = ['main' => 'Основная навигация', 'account' => 'Меню аккаунта', 'tools' => 'Кнопки в шапке', 'categories' => 'Меню категорий'][$area])
        <section class="admin-panel" @if($area === 'categories') id="category-menu" @endif>
            <h2>{{ __($heading) }}</h2>
            @if($area === 'categories')
                <fieldset class="admin-menu-item">
                    <legend>{{ __('Новая категория') }}</legend>
                    <div class="admin-menu-fields">
                        @foreach($languages as $locale => $language)
                            <label lang="{{ $locale }}">{{ $language }}
                                <input form="create-catalog-category" name="names[{{ $locale }}]" value="{{ old("names.$locale") }}" maxlength="80" required>
                            </label>
                        @endforeach
                        <label>{{ __('Адрес категории') }}
                            <input form="create-catalog-category" name="slug" value="{{ old('slug') }}" placeholder="woodworking" maxlength="23" pattern="[a-z][a-z0-9\-]*" required>
                        </label>
                    </div>
                    <button class="button small admin-menu-remove" type="submit" form="create-catalog-category">{{ __('Создать категорию') }}</button>
                </fieldset>
                @if($availableCategories->isNotEmpty())
                    <div class="admin-menu-actions">
                        <label>{{ __('Категория') }}
                            <select name="item" form="add-category-menu-item" required>
                                @foreach($availableCategories as $key => $item)
                                    <option value="{{ $key }}">{{ __($item['label']) }}</option>
                                @endforeach
                            </select>
                        </label>
                        <button class="button small" type="submit" form="add-category-menu-item">{{ __('Добавить пункт') }}</button>
                    </div>
                @endif
                <p>{{ __('Пустая подпись использует название категории из редактора контента.') }}</p>
            @endif
            @foreach($group as $key => $item)
                <fieldset class="admin-menu-item">
                    <legend>{{ __($item['label']) }}</legend>
                    <div class="admin-menu-fields">
                        @foreach($languages as $locale => $language)
                            <label lang="{{ $locale }}">{{ $language }}
                                <input name="items[{{ $key }}][labels][{{ $locale }}]" value="{{ old("items.$key.labels.$locale", $item['labels'][$locale]) }}" maxlength="80" @required(!isset($item['parameters']['category']))>
                            </label>
                        @endforeach
                        <label>{{ __('Порядок') }}
                            <input name="items[{{ $key }}][position]" type="number" min="0" max="999" value="{{ old("items.$key.position", $item['position']) }}" required>
                        </label>
                        <label>{{ __('Видимость') }}
                            <select name="items[{{ $key }}][enabled]">
                                <option value="1" @selected(old("items.$key.enabled", (int) $item['enabled']) == 1)>{{ __('Показывать') }}</option>
                                <option value="0" @selected(old("items.$key.enabled", (int) $item['enabled']) == 0)>{{ __('Скрывать') }}</option>
                            </select>
                        </label>
                    </div>
                    @if($area === 'categories')
                        <button class="button small outline admin-menu-remove" type="submit" form="remove-{{ $key }}">{{ __('Удалить пункт') }}</button>
                    @endif
                </fieldset>
            @endforeach
        </section>
    @endforeach
    <button class="button" type="submit">{{ __('Сохранить изменения') }}</button>
</form>

<form id="create-catalog-category" method="post" action="{{ route('admin.menu.categories.create') }}">@csrf</form>
<form id="add-category-menu-item" method="post" action="{{ route('admin.menu.categories.store') }}">@csrf</form>
@foreach($groups->get('categories', []) as $key => $item)
    <form id="remove-{{ $key }}" method="post" action="{{ route('admin.menu.categories.destroy', ['item' => $key]) }}">
        @csrf
        @method('DELETE')
    </form>
@endforeach
@endsection
