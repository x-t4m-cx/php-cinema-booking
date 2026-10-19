@extends('layouts.app')

@section('title', 'Фильм: ' . $movie->title)

@section('content')
    <div class="page-head">
        <div>
            <h1 class="page-title">Редактирование фильма</h1>
            <p class="page-subtitle">{{ $movie->title }}</p>
        </div>
        <a class="btn btn--ghost" href="{{ route('movies.show', $movie) }}">К карточке</a>
    </div>

    @include('partials.errors')

    <div class="card">
        @include('movies._form', ['movie' => $movie])
    </div>
@endsection
