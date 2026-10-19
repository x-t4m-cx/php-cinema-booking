@extends('layouts.app')

@section('title', $movie->title)

@section('content')
    <div class="page-head">
        <div>
            <h1 class="page-title">{{ $movie->title }}</h1>
            <p class="page-subtitle">Карточка фильма</p>
        </div>
        <div class="page-head__actions">
            <a class="btn btn--primary" href="{{ route('movies.edit', $movie) }}">Изменить</a>
            <form class="inline" method="POST" action="{{ route('movies.destroy', $movie) }}"
                  onsubmit="return confirm('Удалить фильм «{{ $movie->title }}»?');">
                @csrf
                @method('DELETE')
                <button class="btn btn--danger" type="submit">Удалить</button>
            </form>
        </div>
    </div>

    @include('partials.errors')

    <div class="stats">
        <div class="stat">
            <span class="stat__value">{{ $movie->duration_label }}</span>
            <span class="stat__label">длительность</span>
        </div>
        <div class="stat">
            <span class="stat__value">{{ $movie->sessions_count }}</span>
            <span class="stat__label">сеансов</span>
        </div>
    </div>

    <div class="card">
        <h2 class="section-title">Описание</h2>
        <p class="prose">{{ $movie->description ?: 'Описание не заполнено.' }}</p>

        <dl class="details">
            <dt>Добавлен</dt>
            <dd>{{ $movie->created_at->format('d.m.Y H:i') }}</dd>
            <dt>Обновлён</dt>
            <dd>{{ $movie->updated_at->format('d.m.Y H:i') }}</dd>
        </dl>
    </div>

    <div class="page-foot">
        <a class="link" href="{{ route('movies.index') }}">← Все фильмы</a>
    </div>
@endsection
