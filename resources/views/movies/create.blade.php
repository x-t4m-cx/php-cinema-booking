@extends('layouts.app')

@section('title', 'Новый фильм')

@section('content')
    <div class="page-head">
        <div>
            <h1 class="page-title">Новый фильм</h1>
            <p class="page-subtitle">Заполните карточку фильма.</p>
        </div>
        <a class="btn btn--ghost" href="{{ route('movies.index') }}">К списку фильмов</a>
    </div>

    @include('partials.errors')

    <div class="card">
        @include('movies._form', ['movie' => $movie])
    </div>
@endsection
