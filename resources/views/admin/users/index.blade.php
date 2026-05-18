@extends('layouts.app')

@section('title', 'Пользователи — админ')

@section('content')
<div class="sed-page-header">
    <h1>Пользователи</h1>
    <p><a href="{{ route('documents.index') }}">← Документы</a>
        @if(auth()->user()->isAdmin())
            · <a href="{{ route('admin.departments.index') }}">Отделы</a>
        @endif
    </p>
</div>

<div class="sed-card">
    <p style="margin:0 0 1rem;">
        <a class="sed-btn sed-btn--primary sed-btn--sm" href="{{ route('admin.users.create') }}">Новый пользователь</a>
    </p>
    <div class="sed-table-wrap">
        <table class="sed-table">
            <thead>
                <tr>
                    <th>Имя</th>
                    <th>Email</th>
                    <th>Отдел</th>
                    <th>Роль</th>
                    <th>Активен</th>
                    <th style="text-align:right;">Действия</th>
                </tr>
            </thead>
            <tbody>
                @foreach($users as $u)
                    <tr>
                        <td>{{ $u->displayName() }}</td>
                        <td>{{ $u->email }}</td>
                        <td>{{ $u->department?->name ?? '—' }}</td>
                        <td>{{ match ($u->role) {
                            'admin' => 'Администратор',
                            'department_head' => 'Начальник отдела',
                            default => 'Сотрудник',
                        } }}</td>
                        <td>{{ $u->is_active ? 'да' : 'нет' }}</td>
                        <td style="text-align:right;">
                            <a class="sed-btn sed-btn--ghost sed-btn--sm" href="{{ route('admin.users.edit', $u) }}">Изменить</a>
                            @if($u->id !== auth()->id())
                                @if($u->is_active)
                                    <form action="{{ route('admin.users.destroy', $u) }}" method="post" style="display:inline;" data-sed-confirm="Отключить пользователя?">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="sed-btn sed-btn--danger sed-btn--sm">Отключить</button>
                                    </form>
                                @else
                                    <form action="{{ route('admin.users.restore', $u) }}" method="post" style="display:inline;" data-sed-confirm="Включить пользователя? Снова будет доступен вход в систему.">
                                        @csrf
                                        <button type="submit" class="sed-btn sed-btn--primary sed-btn--sm">Включить</button>
                                    </form>
                                @endif
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @include('partials.pagination-prev-next', ['paginator' => $users, 'ariaLabel' => 'Пагинация пользователей'])
</div>
@endsection
