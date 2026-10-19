@extends('layouts.app')

@section('title', 'Зал: ' . $hall->name)

@section('content')
    <div class="page-head">
        <div>
            <h1 class="page-title">Редактирование зала</h1>
            <p class="page-subtitle">{{ $hall->name }} · {{ $hall->rows_count }} × {{ $hall->seats_per_rows }}</p>
        </div>
        <a class="btn btn--ghost" href="{{ route('halls.show', $hall) }}">К сетке зала</a>
    </div>

    @include('partials.errors')

    <div class="card">
        @include('halls._form', ['hall' => $hall])
    </div>
@endsection
