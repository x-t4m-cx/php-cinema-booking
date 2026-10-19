@extends('layouts.app')

@section('title', 'Залы')

@section('content')
    <div class="page-head">
        <div>
            <h1 class="page-title">Залы</h1>
            <p class="page-subtitle">Нажмите на название зала, чтобы открыть сетку мест.</p>
        </div>
        <a class="btn btn--primary" href="{{ route('halls.create') }}">Добавить зал</a>
    </div>

    <div class="card">
        @if ($halls->isEmpty())
            <p class="empty">Залов пока нет.
                <a class="link" href="{{ route('halls.create') }}">Создайте первый зал</a>.</p>
        @else
            <table class="table">
                <thead>
                    <tr>
                        <th>Зал</th>
                        <th>Рядов</th>
                        <th>Мест в ряду</th>
                        <th>Всего мест</th>
                        <th>Сеансов</th>
                        <th class="table__actions">Действия</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($halls as $hall)
                        <tr>
                            <td>
                                <a class="link link--strong" href="{{ route('halls.show', $hall) }}">{{ $hall->name }}</a>
                            </td>
                            <td class="nowrap">{{ $hall->rows_count }}</td>
                            <td class="nowrap">{{ $hall->seats_per_rows }}</td>
                            <td><span class="badge">{{ $hall->seats_count }}</span></td>
                            <td><span class="badge">{{ $hall->sessions_count }}</span></td>
                            <td class="table__actions">
                                <a class="btn btn--sm btn--primary" href="{{ route('halls.show', $hall) }}">Сетка мест</a>
                                <a class="btn btn--sm" href="{{ route('halls.edit', $hall) }}">Изменить</a>
                                <form class="inline" method="POST" action="{{ route('halls.destroy', $hall) }}"
                                      onsubmit="return confirm('Удалить зал «{{ $hall->name }}» вместе с местами?');">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn--sm btn--danger" type="submit">Удалить</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>

    @include('partials.pager', ['paginator' => $halls])
@endsection
