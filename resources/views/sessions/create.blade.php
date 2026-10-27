@extends('layouts.app')

@section('title', 'Новый сеанс')

@section('content')
    <div class="page-head">
        <div>
            <h1 class="page-title">Новый сеанс</h1>
            <p class="page-subtitle">
                Выберите фильм, зал и время начала. Свободные окна зала видны под формой.
            </p>
        </div>
        <a class="btn btn--ghost" href="{{ route('sessions.index') }}">К списку сеансов</a>
    </div>

    @include('partials.errors')

    <div class="card">
        @include('sessions._form')
    </div>

    @include('sessions._availability')
@endsection
