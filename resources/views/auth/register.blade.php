@extends('layouts.app')

@section('title', 'Регистрация')

@section('content')
    <div class="auth">
        <div class="card auth__card">
            <h1 class="auth__title">Регистрация</h1>
            <p class="page-subtitle">
                Аккаунт покупателя: просмотр каталога, а бронирование и оплата появятся позже.
            </p>

            @include('partials.errors')

            <form class="form" method="POST" action="{{ route('register.store') }}">
                @csrf

                <div class="field">
                    <label class="field__label" for="name">Имя</label>
                    <input class="field__input" id="name" name="name" type="text" maxlength="255" required autofocus
                           value="{{ old('name') }}">
                    @error('name')<p class="field__error">{{ $message }}</p>@enderror
                </div>

                <div class="field">
                    <label class="field__label" for="email">Email</label>
                    <input class="field__input" id="email" name="email" type="email" maxlength="255" required
                           value="{{ old('email') }}">
                    @error('email')<p class="field__error">{{ $message }}</p>@enderror
                </div>

                <div class="field">
                    <label class="field__label" for="password">Пароль</label>
                    <input class="field__input" id="password" name="password" type="password" required>
                    <p class="field__hint">Минимум 8 символов.</p>
                    @error('password')<p class="field__error">{{ $message }}</p>@enderror
                </div>

                <div class="field">
                    <label class="field__label" for="password_confirmation">Повторите пароль</label>
                    <input class="field__input" id="password_confirmation" name="password_confirmation" type="password" required>
                </div>

                <div class="form__actions">
                    <button class="btn btn--primary" type="submit">Зарегистрироваться</button>
                </div>
            </form>

            <p class="field__hint auth__hint">
                Уже есть аккаунт?
                <a class="link" href="{{ route('login') }}">Войти</a>.
            </p>
        </div>
    </div>
@endsection
