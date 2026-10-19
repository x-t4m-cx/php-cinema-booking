@extends('layouts.app')

@section('title', $seat->label)

@section('content')
    <div class="page-head">
        <div>
            <h1 class="page-title">{{ $seat->label }}</h1>
            <p class="page-subtitle">
                Зал <a class="link" href="{{ route('halls.show', $seat->hall) }}">{{ $seat->hall->name }}</a>
            </p>
        </div>
        <div class="page-head__actions">
            <a class="btn btn--primary" href="{{ route('seats.edit', $seat) }}">Изменить</a>
            <form class="inline" method="POST" action="{{ route('seats.destroy', $seat) }}"
                  onsubmit="return confirm('Удалить место {{ $seat->label }}?');">
                @csrf
                @method('DELETE')
                <button class="btn btn--danger" type="submit">Удалить</button>
            </form>
        </div>
    </div>

    @include('partials.errors')

    <div class="card">
        <dl class="details">
            <dt>Зал</dt>
            <dd><a class="link" href="{{ route('halls.show', $seat->hall) }}">{{ $seat->hall->name }}</a></dd>
            <dt>Ряд</dt>
            <dd>{{ $seat->row_number }}</dd>
            <dt>Место в ряду</dt>
            <dd>{{ $seat->seat_number }}</dd>
            <dt>Статус</dt>
            <dd>
                <span class="badge {{ $seat->is_active ? 'badge--ok' : 'badge--muted' }}">
                    {{ $seat->is_active ? 'доступно' : 'отключено' }}
                </span>
            </dd>
            <dt>В бронях</dt>
            <dd><span class="badge">{{ $seat->bookingSeats->count() }}</span></dd>
        </dl>
    </div>

    <div class="card">
        <h2 class="section-title">Бронирования места</h2>

        @if ($seat->bookingSeats->isEmpty())
            <p class="empty">Это место пока никто не бронировал.</p>
        @else
            <table class="table">
                <thead>
                    <tr>
                        <th>Фильм</th>
                        <th>Сеанс</th>
                        <th>Гость</th>
                        <th>Статус брони</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($seat->bookingSeats as $bookingSeat)
                        <tr>
                            <td>{{ $bookingSeat->booking->movieSession->movie->title }}</td>
                            <td class="nowrap">{{ $bookingSeat->booking->movieSession->starts_at->format('d.m.Y H:i') }}</td>
                            <td>{{ $bookingSeat->booking->customer_name }}</td>
                            <td><span class="badge">{{ $bookingSeat->booking->status->value }}</span></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>

    <div class="page-foot">
        <a class="link" href="{{ route('halls.show', $seat->hall) }}">← Сетка зала</a>
    </div>
@endsection
