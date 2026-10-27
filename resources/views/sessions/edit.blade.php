@extends('layouts.app')

@section('title', 'Сеанс: ' . $session->movie->title)

@section('content')
    <div class="page-head">
        <div>
            <h1 class="page-title">Редактирование сеанса</h1>
            <p class="page-subtitle">
                {{ $session->movie->title }} · {{ $session->starts_at->format('d.m.Y H:i') }} · {{ $session->hall->name }}
            </p>
        </div>
        <div class="page-head__actions">
            @can('cancel', $session)
                @if ($session->status === App\Enums\MovieSessionStatus::Scheduled && ! $session->isFinished())
                    <form class="inline" method="POST" action="{{ route('sessions.cancel', $session) }}"
                          onsubmit="return confirm('Отменить сеанс? Слот освободится для новых сеансов.');">
                        @csrf
                        @method('PATCH')
                        <button class="btn btn--danger" type="submit">Отменить сеанс</button>
                    </form>
                @endif
            @endcan
            <a class="btn btn--ghost" href="{{ route('sessions.index') }}">К списку сеансов</a>
        </div>
    </div>

    @include('partials.errors')

    <div class="card">
        @include('sessions._form')
    </div>

    @include('sessions._availability')
@endsection
