@extends('layouts.app')

@section('title', 'Вход')

@section('content')
    <div class="auth">
        <div class="card auth__card">
            <h1 class="auth__title">Вход в панель</h1>
            <p class="page-subtitle">Войдите, чтобы управлять афишей или бронировать места.</p>

            @include('partials.errors')

            <form class="form" method="POST" action="{{ route('login.store') }}">
                @csrf

                <div class="field">
                    <label class="field__label" for="email">Email</label>
                    <input class="field__input" id="email" name="email" type="email" maxlength="255" required autofocus
                           value="{{ old('email') }}">
                    @error('email')<p class="field__error">{{ $message }}</p>@enderror
                </div>

                <div class="field">
                    <label class="field__label" for="password">Пароль</label>
                    <input class="field__input" id="password" name="password" type="password" required>
                    @error('password')<p class="field__error">{{ $message }}</p>@enderror
                </div>

                <label class="checkbox">
                    <input name="remember" type="checkbox" value="1">
                    Запомнить меня
                </label>

                <div class="form__actions">
                    <button class="btn btn--primary" type="submit">Войти</button>
                </div>
            </form>

            <p class="field__hint auth__hint">
                Нет аккаунта?
                <a class="link" href="{{ route('register') }}">Зарегистрируйтесь</a>.
            </p>

            @if (app()->isLocal())
                <p class="field__hint auth__demo">
                    Демо-доступы: admin@cinema.test, manager@cinema.test, buyer@cinema.test.
                    Пароль у всех — <b>password</b>.
                </p>
            @endif
        </div>
    </div>
@endsection
