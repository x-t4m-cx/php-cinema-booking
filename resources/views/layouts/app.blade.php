<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Панель кинотеатра')</title>
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
</head>
<body class="layout">
    <header class="topbar">
        <a class="brand" href="{{ route('halls.index') }}">
            <span class="brand__mark">🎬</span>
            <span class="brand__text">Кинотеатр<b>· панель</b></span>
        </a>

        <nav class="topnav">
            <a class="topnav__link @if (request()->routeIs('schedule.*')) is-active @endif"
               href="{{ route('schedule.index') }}">Расписание</a>
            <a class="topnav__link @if (request()->routeIs('halls.*')) is-active @endif"
               href="{{ route('halls.index') }}">Залы</a>
            <a class="topnav__link @if (request()->routeIs('movies.*')) is-active @endif"
               href="{{ route('movies.index') }}">Фильмы</a>
            @can('viewAny', App\Models\MovieSession::class)
                <a class="topnav__link @if (request()->routeIs('sessions.*')) is-active @endif"
                   href="{{ route('sessions.index') }}">Сеансы</a>
            @endcan
            @can('viewAny', App\Models\User::class)
                <a class="topnav__link @if (request()->routeIs('users.*')) is-active @endif"
                   href="{{ route('users.index') }}">Пользователи</a>
            @endcan
        </nav>

        <div class="topbar__account">
            @auth
                <span class="topbar__name">{{ auth()->user()->name }}</span>
                <span class="role role--{{ auth()->user()->role->value }}">{{ auth()->user()->role->label() }}</span>
                <form class="inline" method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button class="btn btn--sm topbar__logout" type="submit">Выйти</button>
                </form>
            @else
                <a class="btn btn--sm topbar__register" href="{{ route('register') }}">Регистрация</a>
                <a class="btn btn--sm topbar__login" href="{{ route('login') }}">Войти</a>
            @endauth
        </div>
    </header>

    <main class="page">
        @if (session('status'))
            <div class="alert alert--success">{{ session('status') }}</div>
        @endif

        @yield('content')
    </main>

    <footer class="footer">
        <span>Бронирование мест в кинотеатре</span>
        <span>{{ now()->format('d.m.Y') }}</span>
    </footer>
</body>
</html>
