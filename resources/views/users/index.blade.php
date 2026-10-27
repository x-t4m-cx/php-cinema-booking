@extends('layouts.app')

@section('title', 'Пользователи')

@section('content')
    <div class="page-head">
        <div>
            <h1 class="page-title">Пользователи</h1>
            <p class="page-subtitle">
                Покупатели и менеджеры панели. Роль администратора через панель не назначается.
            </p>
        </div>
        <a class="btn btn--primary" href="{{ route('users.create') }}">Добавить менеджера</a>
    </div>

    <div class="card">
        @if ($users->isEmpty())
            <p class="empty">Пользователей пока нет.</p>
        @else
            <table class="table">
                <thead>
                    <tr>
                        <th>Имя</th>
                        <th>Email</th>
                        <th>Роль</th>
                        <th class="table__actions">Действия</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($users as $user)
                        <tr>
                            <td>{{ $user->name }}</td>
                            <td class="nowrap">{{ $user->email }}</td>
                            <td>
                                <span class="role role--{{ $user->role->value }}">{{ $user->role->label() }}</span>
                            </td>
                            <td class="table__actions">
                                @if ($user->isAdmin())
                                    <span class="badge badge--muted">не изменяется</span>
                                @else
                                    <form class="role-form" method="POST" action="{{ route('users.update', $user) }}">
                                        @csrf
                                        @method('PATCH')
                                        <select class="field__input field__input--compact" name="role"
                                                aria-label="Роль пользователя {{ $user->name }}">
                                            @foreach ($roles as $role)
                                                <option value="{{ $role->value }}" @selected($user->role === $role)>{{ $role->label() }}</option>
                                            @endforeach
                                        </select>
                                        <button class="btn btn--sm" type="submit">Сохранить</button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>

    @include('partials.pager', ['paginator' => $users])
@endsection
