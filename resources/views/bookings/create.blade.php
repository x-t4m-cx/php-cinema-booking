@extends('layouts.app')

@section('title', 'Бронирование: ' . $session->movie->title)

@section('content')
    <div class="page-head">
        <div>
            <h1 class="page-title">Бронирование</h1>
            <p class="page-subtitle">
                {{ $session->movie->title }} · {{ $session->hall->name }} ·
                {{ $session->starts_at->format('d.m.Y H:i') }}
            </p>
        </div>
        <a class="btn btn--ghost"
           href="{{ route('schedule.index', ['date' => $session->starts_at->toDateString()]) }}">
            К расписанию
        </a>
    </div>

    <div class="stats">
        <div class="stat">
            <span class="stat__value">{{ $session->starts_at->format('H:i') }}</span>
            <span class="stat__label">начало</span>
        </div>
        <div class="stat">
            <span class="stat__value">{{ $session->ends_at->format('H:i') }}</span>
            <span class="stat__label">окончание</span>
        </div>
        <div class="stat">
            <span class="stat__value">{{ number_format((float) $session->price, 0, ',', ' ') }} ₽</span>
            <span class="stat__label">цена билета</span>
        </div>
    </div>

    <div class="card">
        <p class="empty">Выбор мест и оплата появятся позже.</p>
    </div>
@endsection
