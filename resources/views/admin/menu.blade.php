@extends('admin.layout')

@section('title', __('Меню витрины').' — Handmade Work')
@section('admin-heading', __('Меню витрины'))

@section('admin-content')
@include('errors')

<section class="admin-panel" id="category-menu">
    <h2>{{ __('Меню категорий') }}</h2>
    <form method="post" action="{{ route('admin.menu.categories.create') }}">
        @csrf
        <fieldset class="admin-menu-item">
            <legend>{{ __('Новая категория') }}</legend>
            <div class="admin-menu-fields">
                @foreach($languages as $locale => $language)
                    <label lang="{{ $locale }}">{{ $language }}
                        <input name="names[{{ $locale }}]" value="{{ old("names.$locale") }}" maxlength="80" required>
                    </label>
                @endforeach
                <label>{{ __('Адрес категории') }}
                    <input name="slug" value="{{ old('slug') }}" placeholder="woodworking" maxlength="23" required>
                </label>
            </div>
            <button class="button small admin-menu-create" type="submit">{{ __('Создать категорию') }}</button>
        </fieldset>
    </form>
    <h3>{{ __('Созданные категории') }}</h3>
    @if(session('category_delete_error'))
        <p class="notice" role="alert">{{ session('category_delete_error') }}</p>
    @endif
    @if($categories->isNotEmpty())
        <p>{{ __('В каждой части меню пункты с меньшим номером показываются первыми.') }}</p>
        <ul class="admin-category-list">
            @foreach($categories as $item)
                @php
                    $slug = $item['parameters']['category'];
                    $name = $item['label'];
                @endphp
                <li id="catalog-category-{{ $slug }}">
                    <span><strong>{{ $name }}</strong> <small>{{ $slug }}</small></span>
                    <div>
                        <label class="admin-category-order">{{ __('Порядок') }}
                            <input form="category-order" name="positions[{{ $slug }}]" type="number" min="0" max="999"
                                value="{{ old("positions.$slug", $item['position']) }}" required>
                        </label>
                        <a class="text-link" href="{{ route('catalog', ['category' => $slug]) }}">{{ __('Открыть на витрине') }}</a>
                        <a class="text-link" href="{{ route('admin.editorial.edit', [$slug, app()->getLocale()]) }}">{{ __('Редактировать') }}</a>
                        <form action="{{ route('admin.catalog.categories.destroy', $slug) }}" method="post"
                            data-confirm="{{ __('Удалить категорию «:name»?', ['name' => $name]) }}" onsubmit="return confirm(this.dataset.confirm)">
                            @csrf
                            @method('DELETE')
                            <button class="button small outline" type="submit">{{ __('Удалить категорию') }}</button>
                        </form>
                    </div>
                </li>
            @endforeach
        </ul>
        <form id="category-order" class="admin-menu-create" action="{{ route('admin.catalog.categories.order') }}" method="post">
            @csrf
            @method('PATCH')
            <button type="submit" class="button">{{ __('Сохранить порядок категорий') }}</button>
        </form>
    @else
        <p>{{ __('Пока нет категорий.') }}</p>
    @endif
</section>

<form class="stack" method="post" action="{{ route('admin.menu.update') }}">
    @csrf
    @method('PUT')
    <p>{{ __('В каждой части меню пункты с меньшим номером показываются первыми.') }}</p>
    @foreach($items->groupBy('area', preserveKeys: true) as $area => $group)
        @php($heading = ['main' => 'Основная навигация', 'account' => 'Меню аккаунта', 'tools' => 'Кнопки в шапке'][$area])
        <section class="admin-panel">
            <h2>{{ __($heading) }}</h2>
            @foreach($group as $key => $item)
                <fieldset class="admin-menu-item">
                    <legend>{{ __($item['label']) }}</legend>
                    <div class="admin-menu-fields">
                        @foreach($languages as $locale => $language)
                            <label lang="{{ $locale }}">{{ $language }}
                                <input name="items[{{ $key }}][labels][{{ $locale }}]" value="{{ old("items.$key.labels.$locale", $item['labels'][$locale]) }}" maxlength="80" required>
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
                </fieldset>
            @endforeach
        </section>
    @endforeach
    <button class="button" type="submit">{{ __('Сохранить изменения') }}</button>
</form>

@endsection
