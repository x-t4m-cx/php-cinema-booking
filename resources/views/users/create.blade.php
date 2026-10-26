@extends('layouts.app')

@section('title', 'Новый менеджер')

@section('content')
    <div class="page-head">
        <div>
            <h1 class="page-title">Новый менеджер</h1>
            <p class="page-subtitle">Сотрудник получит роль «Менеджер» и сможет добавлять фильмы.</p>
        </div>
        <a class="btn btn--ghost" href="{{ route('users.index') }}">К списку пользователей</a>
    </div>

    @include('partials.errors')

    <div class="card">
        @include('users._form')
    </div>
@endsection
