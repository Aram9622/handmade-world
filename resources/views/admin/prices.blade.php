@extends('admin.layout')
@section('title', __('Цены').' — Handmade Work')
@section('admin-heading', __('Цены'))

@section('admin-content')
<section class="panel">
    @include('errors')
    <form class="stack" action="{{ route('admin.prices.update') }}" method="post">
        @csrf
        @method('PUT')
        <label>
            <span>
                <input type="hidden" name="use_default_price" value="0">
                <input type="checkbox" name="use_default_price" value="1" @checked(old('use_default_price', $settings->use_default_price))>
                {{ __('Подставлять цену по умолчанию в новых объявлениях') }}
            </span>
        </label>
        <label>{{ __('Цена по умолчанию в ₽') }}
            <input type="number" name="default_price" min="1" max="10000000" value="{{ old('default_price', $settings->default_price) }}">
        </label>
        <div><button class="button" type="submit">{{ __('Сохранить изменения') }}</button></div>
    </form>
</section>
@endsection
