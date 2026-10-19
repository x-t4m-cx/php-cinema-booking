@extends('layouts.app')

@section('title', 'Место: ' . $seat->label)

@section('content')
    <div class="page-head">
        <div>
            <h1 class="page-title">Редактирование места</h1>
            <p class="page-subtitle">
                Зал «{{ $hall->name }}» · {{ $hall->rows_count }} рядов, {{ $hall->seats_per_rows }} мест в ряду
            </p>
        </div>
        <a class="btn btn--ghost" href="{{ route('halls.show', $hall) }}">К сетке зала</a>
    </div>

    @include('partials.errors')

    <div class="card">
        @include('seats._form', ['seat' => $seat, 'hall' => $hall])
    </div>
@endsection
