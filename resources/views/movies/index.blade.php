@extends('layouts.app')

@section('title', 'Фильмы')

@section('content')
    <div class="page-head">
        <div>
            <h1 class="page-title">Фильмы</h1>
            <p class="page-subtitle">Каталог фильмов и количество назначенных сеансов.</p>
        </div>
        @can('create', App\Models\Movie::class)
            <a class="btn btn--primary" href="{{ route('movies.create') }}">Добавить фильм</a>
        @endcan
    </div>

    <div class="card">
        @if ($movies->isEmpty())
            <p class="empty">
                В каталоге пока нет фильмов.
                @can('create', App\Models\Movie::class)
                    <a class="link" href="{{ route('movies.create') }}">Добавьте первый фильм</a>.
                @endcan
            </p>
        @else
            <table class="table">
                <thead>
                    <tr>
                        <th>Название</th>
                        <th>Длительность</th>
                        <th>Сеансов</th>
                        <th class="table__actions">Действия</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($movies as $movie)
                        <tr>
                            <td>
                                <a class="link link--strong" href="{{ route('movies.show', $movie) }}">{{ $movie->title }}</a>
                                @unless ($movie->is_active)
                                    <span class="badge badge--muted">снят с показа</span>
                                @endunless
                                @if ($movie->description)
                                    <span class="muted">{{ Str::limit($movie->description, 90) }}</span>
                                @endif
                            </td>
                            <td class="nowrap">{{ $movie->duration_label }}</td>
                            <td><span class="badge">{{ $movie->sessions_count }}</span></td>
                            <td class="table__actions">
                                <a class="btn btn--sm btn--ghost" href="{{ route('movies.show', $movie) }}">Просмотр</a>
                                @can('update', $movie)
                                    <a class="btn btn--sm" href="{{ route('movies.edit', $movie) }}">Изменить</a>
                                @endcan
                                @can('delete', $movie)
                                    <form class="inline" method="POST" action="{{ route('movies.destroy', $movie) }}"
                                          onsubmit="return confirm('Удалить фильм «{{ $movie->title }}»?');">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn--sm btn--danger" type="submit">Удалить</button>
                                    </form>
                                @endcan
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>

    @include('partials.pager', ['paginator' => $movies])
@endsection
