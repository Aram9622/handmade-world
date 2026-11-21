@extends('admin.layout')
@section('title', __('Контент').' — Handmade Work')

@section('admin-heading', __('Контент'))

@section('admin-content')
@php
    $pages = \App\Services\EditorialContent::PAGES;
    $languages = \App\Http\Middleware\SetLocale::LANGUAGES;
    $fields = [
        'title' => ['Название', 100],
        'heading' => ['Подзаголовок', 140],
        'alt' => ['Описание изображения', 180],
        'cta' => ['Текст кнопки', 80],
    ] + ($page === 'home' ? ['directory_title' => ['Заголовок категорий', 140]] : [
        'history_title' => ['Заголовок истории', 140],
        'gallery_title' => ['Заголовок галереи', 140],
    ]);
@endphp
    <nav class="admin-editor-tabs" aria-label="{{ __('Страницы витрины') }}">
        @foreach($pages as $key => $label)
            <a class="text-link" href="{{ route('admin.editorial.edit', [$key, $language]) }}" @if($page === $key) aria-current="page" @endif>{{ __($label) }}</a>
        @endforeach
    </nav>
    <nav class="admin-editor-tabs" aria-label="{{ __('Язык контента') }}">
        @foreach($languages as $key => $label)
            <a class="text-link" lang="{{ $key }}" href="{{ route('admin.editorial.edit', [$page, $key]) }}" @if($language === $key) aria-current="page" @endif>{{ $label }}</a>
        @endforeach
    </nav>

    @include('errors')

    <form class="stack admin-editor" action="{{ route('admin.editorial.update', [$page, $language]) }}" method="post" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        <section class="admin-panel">
            <h2>{{ __($pages[$page]) }} · {{ $languages[$language] }}</h2>
            <div class="admin-editor-layout">
                <div class="admin-editor-fields">
                    @foreach($fields as $name => [$label, $limit])
                        <label>{{ __($label) }}
                            <input name="{{ $name }}" value="{{ old($name, $content[$name]) }}" maxlength="{{ $limit }}" @required($name !== 'heading') @error($name) aria-invalid="true" @enderror>
                        </label>
                    @endforeach
                    <label class="editor-wide">{{ __('Описание') }}
                        <textarea name="intro" rows="4" maxlength="1500" required @error('intro') aria-invalid="true" @enderror>{{ old('intro', $content['intro']) }}</textarea>
                    </label>
                </div>
                <div class="admin-editor-cover">
                    <img class="editorial-thumbnail" src="{{ $content['cover_url'] }}" alt="{{ $content['alt'] }}">
                    <label>{{ __('Заменить обложку') }}
                        <small id="cover-formats">{{ __('JPG, PNG или WebP, до 5 МБ') }}</small>
                        <input type="file" name="cover" accept="image/jpeg,image/png,image/webp" aria-describedby="cover-formats" @error('cover') aria-invalid="true" @enderror>
                    </label>
                </div>
            </div>
        </section>

        @if($page !== 'home')
            <section class="admin-panel">
                <h2>{{ __('Хронология') }}</h2>
                <p>{{ __('Оставьте все поля строки пустыми, чтобы убрать её со страницы.') }}</p>
                @foreach(range(0, 5) as $index)
                    @php($entry = $content['timeline'][$index] ?? [])
                    <details class="editorial-row" @if(old("timeline.$index.title", $entry['title'] ?? null)) open @endif>
                        <summary>{{ old("timeline.$index.title", $entry['title'] ?? null) ?: __('Добавить этап') }}</summary>
                        <div class="admin-editor-fields">
                            <label>{{ __('Период') }}<input name="timeline[{{ $index }}][period]" maxlength="100" value="{{ old("timeline.$index.period", $entry['period'] ?? '') }}"></label>
                            <label>{{ __('Название') }}<input name="timeline[{{ $index }}][title]" maxlength="140" value="{{ old("timeline.$index.title", $entry['title'] ?? '') }}"></label>
                            <label class="editor-wide">{{ __('Описание') }}<textarea name="timeline[{{ $index }}][description]" rows="3" maxlength="700">{{ old("timeline.$index.description", $entry['description'] ?? '') }}</textarea></label>
                        </div>
                    </details>
                @endforeach
            </section>
            <section class="admin-panel">
                <h2>{{ __('Галерея') }}</h2>
                @foreach(range(0, 3) as $index)
                    @php($work = $content['works'][$index] ?? [])
                    <details class="editorial-row" @if(old("works.$index.title", $work['title'] ?? null)) open @endif>
                        <summary>{{ old("works.$index.title", $work['title'] ?? null) ?: __('Добавить работу') }}</summary>
                        <div class="admin-editor-fields">
                            <label>{{ __('Название') }}<input name="works[{{ $index }}][title]" maxlength="180" value="{{ old("works.$index.title", $work['title'] ?? '') }}"></label>
                            <label>{{ __('Источник') }}<input name="works[{{ $index }}][museum]" maxlength="120" value="{{ old("works.$index.museum", $work['museum'] ?? '') }}"></label>
                            <label class="editor-wide">{{ __('Ссылка на оригинал') }}<input type="url" name="works[{{ $index }}][url]" maxlength="1000" value="{{ old("works.$index.url", $work['url'] ?? '') }}"></label>
                            <div class="editor-wide">
                                @if(!empty($work['image_url']))<img class="editorial-thumbnail" src="{{ $work['image_url'] }}" alt="{{ $work['title'] }}">@endif
                                <label>{{ __('Фотография') }}<input type="file" name="works[{{ $index }}][image]" accept="image/jpeg,image/png,image/webp"></label>
                            </div>
                        </div>
                    </details>
                @endforeach
            </section>
        @endif
        <button class="button" type="submit">{{ __('Сохранить изменения') }}</button>
    </form>
@endsection
