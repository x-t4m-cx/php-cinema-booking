@extends('layouts.app')

@section('title', 'Новый зал')

@section('content')
    <div class="page-head">
        <div>
            <h1 class="page-title">Новый зал</h1>
            <p class="page-subtitle">После сохранения места зала будут созданы автоматически.</p>
        </div>
        <a class="btn btn--ghost" href="{{ route('halls.index') }}">К списку залов</a>
    </div>

    @include('partials.errors')

    <div class="card">
        @include('halls._form', ['hall' => $hall])
    </div>
@endsection
