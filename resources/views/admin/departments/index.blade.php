@extends('layouts.app')

@section('title', 'Отделы — админ')

@section('content')
<div class="sed-page-header">
    <h1>Отделы</h1>
    <p><a href="{{ route('documents.index') }}">← Документы</a> · <a href="{{ route('admin.users.index') }}">Пользователи</a></p>
</div>

<div class="sed-card">
    <div class="sed-table-wrap">
        <table class="sed-table">
            <thead>
                <tr>
                    <th>Название</th>
                    <th>Главный отдела</th>
                    <th style="text-align:right;">Действия</th>
                </tr>
            </thead>
            <tbody>
                @foreach($departments as $d)
                    <tr>
                        <td>{{ $d->name }}</td>
                        <td>{{ $d->head?->displayName() ?? '—' }}</td>
                        <td style="text-align:right;">
                            <a class="sed-btn sed-btn--ghost sed-btn--sm" href="{{ route('admin.departments.edit', $d) }}">Изменить</a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @include('partials.pagination-prev-next', ['paginator' => $departments, 'ariaLabel' => 'Пагинация по отделам'])
</div>
@endsection
