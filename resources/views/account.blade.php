@extends('layout')
@section('title', ($register ? __('Регистрация') : __('Вход')).' — Handmade Work')

@push('metadata')
    <meta name="robots" content="noindex, follow">
@endpush

@section('content')
<section class="panel">
    <h1>{{ $register ? __('Создать аккаунт') : __('Войти') }}</h1>
    @include('errors')
    <form class="stack" action="{{ route($register ? 'register' : 'login') }}" method="post">
        @csrf
        @if($register)
            <label>{{ __('Имя') }}<input name="name" value="{{ old('name') }}" autocomplete="name" maxlength="80" required></label>
        @endif
        <label>{{ __('Электронная почта') }}<input name="email" type="email" value="{{ old('email') }}" autocomplete="email" maxlength="254" required></label>
        <label>{{ __('Пароль') }}<input name="password" type="password" autocomplete="{{ $register ? 'new-password' : 'current-password' }}" maxlength="72" @if($register) minlength="8" @endif required></label>
        @if($register)
            <label>{{ __('Повторите пароль') }}<input name="password_confirmation" type="password" autocomplete="new-password" minlength="8" maxlength="72" required></label>
        @endif
        <button class="button" type="submit">{{ $register ? __('Зарегистрироваться') : __('Войти') }}</button>
    </form>
    <p><a class="text-link" href="{{ route($register ? 'login' : 'register') }}">{{ $register ? __('Уже есть аккаунт? Войти') : __('Нет аккаунта? Зарегистрироваться') }}</a></p>
</section>
@endsection
