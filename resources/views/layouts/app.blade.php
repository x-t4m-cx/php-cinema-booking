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
            <a class="topnav__link @if (request()->routeIs('halls.*')) is-active @endif"
               href="{{ route('halls.index') }}">Залы</a>
            <a class="topnav__link @if (request()->routeIs('movies.*')) is-active @endif"
               href="{{ route('movies.index') }}">Фильмы</a>
        </nav>
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
