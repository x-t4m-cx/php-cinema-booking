@extends('layouts.app')

@php($weekdays = [1 => 'Пн', 2 => 'Вт', 3 => 'Ср', 4 => 'Чт', 5 => 'Пт', 6 => 'Сб', 7 => 'Вс'])

@section('title', $showAll ? 'Все сеансы' : 'Расписание на ' . $selected->format('d.m.Y'))

@section('content')
    <div class="page-head">
        <div>
            <h1 class="page-title">Расписание</h1>
            <p class="page-subtitle">
                @if ($showAll)
                    Все будущие сеансы с сегодняшнего дня.
                @else
                    Сеансы на {{ $selected->format('d.m.Y') }}.
                @endif
            </p>
        </div>
    </div>

    <div class="schedule-bar">
        <div class="date-strip">
            @unless ($showAll)
                @if ($previousDate->lt($selected))
                    <a class="btn btn--sm date-strip__arrow"
                       href="{{ route('schedule.index', ['date' => $previousDate->toDateString()]) }}"
                       aria-label="Предыдущие даты">←</a>
                @else
                    <span class="btn btn--sm date-strip__arrow is-disabled" aria-disabled="true">←</span>
                @endif
            @endunless

            @foreach ($days as $day)
                <a class="date-chip @if ($day->isToday()) is-today @endif @if (! $showAll && $selected->isSameDay($day)) is-active @endif"
                   href="{{ route('schedule.index', ['date' => $day->toDateString()]) }}">
                    <span class="date-chip__weekday">{{ $weekdays[$day->isoWeekday()] }}</span>
                    <span class="date-chip__date">{{ $day->format('d.m') }}</span>
                    @if ($day->isToday())
                        <span class="date-chip__today">сегодня</span>
                    @endif
                </a>
            @endforeach

            @unless ($showAll)
                <a class="btn btn--sm date-strip__arrow"
                   href="{{ route('schedule.index', ['date' => $nextDate->toDateString()]) }}"
                   aria-label="Следующие даты">→</a>
            @endunless
        </div>

        <div class="schedule-actions">
            @if ($showAll)
                <a class="btn btn--primary" href="{{ route('schedule.index') }}">Вернуться к дате</a>
            @else
                @unless ($selected->isToday())
                    <a class="btn btn--ghost" href="{{ route('schedule.index') }}">Сегодня</a>
                @endunless
                <a class="btn" href="{{ route('schedule.index', ['date' => 'all']) }}">Показать все сеансы</a>
            @endif
        </div>
    </div>

    @if (! $hasUpcoming)
        <div class="card">
            <p class="empty">Расписание пока не опубликовано.</p>
        </div>
    @elseif ($showAll)
        @foreach ($dayGroups as $dayGroup)
            <section class="schedule-day">
                <h2 class="schedule-day__title">
                    {{ $dayGroup['date']->format('d.m.Y') }},
                    {{ $weekdays[$dayGroup['date']->isoWeekday()] }}
                    @if ($dayGroup['date']->isToday())
                        · сегодня
                    @endif
                </h2>

                <div class="card">
                    @foreach ($dayGroup['groups'] as $group)
                        @include('schedule._movie', ['group' => $group])
                    @endforeach
                </div>
            </section>
        @endforeach
    @elseif ($groups->isEmpty())
        <div class="card">
            <p class="empty">На эту дату сеансов нет, выберите другой день.</p>
            <p class="schedule-empty__action">
                <a class="btn btn--primary" href="{{ route('schedule.index', ['date' => 'all']) }}">
                    Показать все сеансы
                </a>
            </p>
        </div>
    @else
        <div class="card">
            @foreach ($groups as $group)
                @include('schedule._movie', ['group' => $group])
            @endforeach
        </div>
    @endif
@endsection
