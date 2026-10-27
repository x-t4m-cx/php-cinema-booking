@extends('layouts.app')

@section('title', $hall->name)

@section('content')
    <div class="page-head">
        <div>
            <h1 class="page-title">{{ $hall->name }}</h1>
            <p class="page-subtitle">
                {{ $hall->rows_count }} рядов · {{ $hall->seats_per_rows }} мест в ряду · {{ $stats['total'] }} мест всего
                @unless ($hall->is_active)
                    · <span class="badge badge--muted">закрыт</span>
                @endunless
            </p>
        </div>
        @can('update', $hall)
            <div class="page-head__actions">
                <a class="btn btn--primary" href="{{ route('halls.seats.create', $hall) }}">Добавить место</a>
                <a class="btn" href="{{ route('halls.edit', $hall) }}">Изменить зал</a>
                <form class="inline" method="POST" action="{{ route('halls.destroy', $hall) }}"
                      onsubmit="return confirm('Удалить зал «{{ $hall->name }}» вместе с местами?');">
                    @csrf
                    @method('DELETE')
                    <button class="btn btn--danger" type="submit">Удалить</button>
                </form>
            </div>
        @endcan
    </div>

    @include('partials.errors')

    <div class="stats">
        <div class="stat">
            <span class="stat__value">{{ $stats['total'] }}</span>
            <span class="stat__label">всего мест</span>
        </div>
        <div class="stat">
            <span class="stat__value">{{ $stats['active'] }}</span>
            <span class="stat__label">доступно</span>
        </div>
        <div class="stat">
            <span class="stat__value">{{ $stats['inactive'] }}</span>
            <span class="stat__label">отключено</span>
        </div>
        <div class="stat">
            <span class="stat__value">{{ $stats['booked'] }}</span>
            <span class="stat__label">в бронях</span>
        </div>
        <div class="stat">
            <span class="stat__value">{{ $hall->sessions_count }}</span>
            <span class="stat__label">сеансов</span>
        </div>
    </div>

    <div class="card">
        <div class="card__head">
            <h2 class="section-title">Сетка мест</h2>
            <p class="muted">Нажмите на место, чтобы открыть его карточку.</p>
        </div>

        @if (empty($grid))
            <p class="empty">
                У зала ещё нет мест.
                @can('update', $hall)
                    <a class="link" href="{{ route('halls.edit', $hall) }}">Задайте размеры зала</a>, чтобы создать сетку.
                @endcan
            </p>
        @else
            @include('halls._seat-grid', ['grid' => $grid])

            <ul class="legend">
                <li class="legend__item"><span class="seat seat--active seat--legend">1</span> доступно</li>
                <li class="legend__item"><span class="seat seat--booked seat--legend">2</span> есть брони</li>
                <li class="legend__item"><span class="seat seat--inactive seat--legend">3</span> отключено</li>
            </ul>
        @endif
    </div>

    <div class="page-foot">
        <a class="link" href="{{ route('halls.index') }}">← Все залы</a>
    </div>
@endsection
