@extends('layouts.app')

@section('title', 'Новый пользователь — админ')

@section('content')
<div class="sed-page-header">
    <h1>Новый пользователь</h1>
    <p><a href="{{ route('admin.users.index') }}">← К списку пользователей</a></p>
</div>

<div class="sed-card" style="max-width:36rem;">
    <form method="post" action="{{ route('admin.users.store') }}">
        @csrf
        <div class="sed-field">
            <label for="name">Логин (name) <span class="sed-req">*</span></label>
            <input class="sed-input" id="name" name="name" required maxlength="255" value="{{ old('name') }}">
        </div>
        <div class="sed-field">
            <label for="full_name">ФИО</label>
            <input class="sed-input" id="full_name" name="full_name" maxlength="255" value="{{ old('full_name') }}">
        </div>
        <div class="sed-field">
            <label for="email">Email <span class="sed-req">*</span></label>
            <input class="sed-input" id="email" name="email" type="email" required value="{{ old('email') }}">
        </div>
        <div class="sed-field">
            <label for="phone">Телефон</label>
            <input class="sed-input" id="phone" name="phone" value="{{ old('phone') }}">
        </div>
        <div class="sed-field">
            <label for="position">Должность</label>
            <input class="sed-input" id="position" name="position" maxlength="255" value="{{ old('position') }}" placeholder="Необязательно">
        </div>
        <div class="sed-field">
            <label for="department_id">Отдел <span class="sed-req">*</span></label>
            <select class="sed-select" id="department_id" name="department_id" required>
                @foreach($departments as $d)
                    <option value="{{ $d->id }}" @selected(old('department_id') == $d->id)>{{ $d->name }}</option>
                @endforeach
            </select>
        </div>
        @if(auth()->user()->isAdmin())
            <div class="sed-field">
                <label for="role">Роль <span class="sed-req">*</span></label>
                <select class="sed-select" id="role" name="role" required>
                    <option value="user" @selected(old('role', 'user') === 'user')>Сотрудник</option>
                    <option value="admin" @selected(old('role') === 'admin')>Администратор</option>
                </select>
            </div>
        @else
            <input type="hidden" name="role" value="user">
        @endif
        <div class="sed-field">
            <label for="password">Пароль <span class="sed-req">*</span></label>
            <input class="sed-input" id="password" name="password" type="password" required minlength="8" autocomplete="new-password">
        </div>
        <div class="sed-field">
            <label for="password_confirmation">Повтор пароля <span class="sed-req">*</span></label>
            <input class="sed-input" id="password_confirmation" name="password_confirmation" type="password" required minlength="8" autocomplete="new-password">
        </div>
        <div class="sed-field">
            <label class="sed-check-row">
                <input type="checkbox" name="is_active" value="1" @checked(old('is_active', true))>
                <span>Аккаунт активен</span>
            </label>
        </div>
        <div style="display:flex;gap:0.5rem;margin-top:1rem;">
            <button type="submit" class="sed-btn sed-btn--primary sed-btn--sm">Создать</button>
            <a class="sed-btn sed-btn--ghost sed-btn--sm" href="{{ route('admin.users.index') }}">Отмена</a>
        </div>
    </form>
</div>
@endsection
