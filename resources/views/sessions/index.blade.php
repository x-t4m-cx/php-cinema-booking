@extends('layouts.app')

@section('title', 'Сеансы')

@section('content')
    <div class="page-head">
        <div>
            <h1 class="page-title">Сеансы</h1>
            <p class="page-subtitle">
                Расписание по залам. Между сеансами учитывается буфер
                {{ App\Models\MovieSession::BUFFER_MINUTES }} минут.
            </p>
        </div>
        <a class="btn btn--primary" href="{{ route('sessions.create') }}">Добавить сеанс</a>
    </div>

    <div class="card">
        @if ($sessions->isEmpty())
            <p class="empty">
                Сеансов пока нет.
                <a class="link" href="{{ route('sessions.create') }}">Добавьте первый сеанс</a>.
            </p>
        @else
            <table class="table">
                <thead>
                    <tr>
                        <th>Начало</th>
                        <th>Фильм</th>
                        <th>Зал</th>
                        <th>Окончание</th>
                        <th>Цена</th>
                        <th>Статус</th>
                        <th class="table__actions">Действия</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($sessions as $session)
                        <tr>
                            <td class="nowrap">{{ $session->starts_at->format('d.m.Y H:i') }}</td>
                            <td>{{ $session->movie->title }}</td>
                            <td>{{ $session->hall->name }}</td>
                            <td class="nowrap">{{ $session->ends_at->format('d.m.Y H:i') }}</td>
                            <td class="nowrap">{{ number_format((float) $session->price, 2, ',', ' ') }} ₽</td>
                            <td>
                                @if ($session->status === App\Enums\MovieSessionStatus::Cancelled)
                                    <span class="badge badge--muted">отменён</span>
                                @elseif ($session->isFinished())
                                    <span class="badge badge--muted">завершён</span>
                                @else
                                    <span class="badge badge--ok">активен</span>
                                @endif
                            </td>
                            <td class="table__actions">
                                @if ($session->status === App\Enums\MovieSessionStatus::Scheduled && ! $session->isFinished())
                                    <a class="btn btn--sm" href="{{ route('sessions.edit', $session) }}">Изменить</a>
                                    <form class="inline" method="POST" action="{{ route('sessions.cancel', $session) }}"
                                          onsubmit="return confirm('Отменить сеанс? Слот освободится для новых сеансов.');">
                                        @csrf
                                        @method('PATCH')
                                        <button class="btn btn--sm btn--danger" type="submit">Отменить</button>
                                    </form>
                                @elseif ($session->status === App\Enums\MovieSessionStatus::Cancelled)
                                    <span class="badge badge--muted">слот свободен</span>
                                @else
                                    <span class="badge badge--muted">сеанс прошёл</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>

    @include('partials.pager', ['paginator' => $sessions])
@endsection
