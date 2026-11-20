@extends('layout')
@section('title', 'Продать изделие — Handmade Work')

@section('content')
<section class="panel">
    <h1>Продать изделие</h1>
    @include('errors')
    <form class="stack" action="{{ route('listings.store') }}" method="post" enctype="multipart/form-data">
        @csrf
        <label>Название<input name="title" maxlength="100" value="{{ old('title') }}" required></label>
        <label>Категория<select name="category" required><option value="">Выберите категорию</option>
        @foreach(\App\Models\Listing::CATEGORIES as $key => $name)
            <option value="{{ $key }}" @selected(old('category') === $key)>{{ $name }}</option>
        @endforeach
        </select></label>
        <label>Описание<textarea name="description" rows="5" maxlength="2000" placeholder="Расскажите о материалах, размере и особенностях изделия" required>{{ old('description') }}</textarea></label>
        <label>Цена в ₽<input type="number" name="price" min="1" max="10000000" value="{{ old('price') }}" required></label>
        <label>Фотография <small>JPG, PNG или WebP, до 5 МБ</small><input type="file" name="photo" accept="image/jpeg,image/png,image/webp" required></label>
        <label>Почта для покупателей <small>Адрес будет виден в объявлении.</small><input type="email" name="contact_email" maxlength="254" value="{{ old('contact_email') }}" required></label>
        <button class="button" type="submit">Опубликовать объявление</button>
    </form>
</section>
@endsection
