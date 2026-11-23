@extends('admin.layout')

@section('title', __('Меню витрины').' — Handmade Work')
@section('admin-heading', __('Меню витрины'))

@section('admin-content')
@include('errors')

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
</section>
@endsection
