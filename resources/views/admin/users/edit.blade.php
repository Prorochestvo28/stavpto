@extends('layouts.app')

@section('title', 'Пользователь — '.$editUser->email)

@section('content')
<div class="sed-page-header">
    <h1>Редактирование</h1>
    <p><a href="{{ route('admin.users.index') }}">← К списку</a></p>
</div>

<div class="sed-card" style="max-width:36rem;">
    <form method="post" action="{{ route('admin.users.update', $editUser) }}">
        @csrf
        @method('PUT')
        <div class="sed-field">
            <label for="name">Логин (name) <span class="sed-req">*</span></label>
            <input class="sed-input" id="name" name="name" required maxlength="255" value="{{ old('name', $editUser->name) }}">
        </div>
        <div class="sed-field">
            <label for="full_name">ФИО</label>
            <input class="sed-input" id="full_name" name="full_name" maxlength="255" value="{{ old('full_name', $editUser->full_name) }}">
        </div>
        <div class="sed-field">
            <label for="email">Email <span class="sed-req">*</span></label>
            <input class="sed-input" id="email" name="email" type="email" required value="{{ old('email', $editUser->email) }}">
        </div>
        <div class="sed-field">
            <label for="phone">Телефон</label>
            <input class="sed-input" id="phone" name="phone" value="{{ old('phone', $editUser->phone) }}">
        </div>
        @if($editUser->id === auth()->id())
            <div class="sed-field">
                <span class="sed-muted" style="font-size:0.9rem;">Должность</span>
                <div style="margin-top:0.35rem;padding:0.5rem 0.65rem;background:var(--surface-muted,#f5f5f5);border-radius:4px;">{{ $editUser->position ?: '—' }}</div>
                <p class="sed-muted" style="margin:0.35rem 0 0;font-size:0.85rem;">Свою должность можно менять только через администратора.</p>
            </div>
        @else
            <div class="sed-field">
                <label for="position">Должность</label>
                <input class="sed-input" id="position" name="position" maxlength="255" value="{{ old('position', $editUser->position) }}" placeholder="Необязательно">
            </div>
        @endif
        <div class="sed-field">
            <label for="department_id">Отдел <span class="sed-req">*</span></label>
            <select class="sed-select" id="department_id" name="department_id" required>
                @foreach($departments as $d)
                    <option value="{{ $d->id }}" @selected(old('department_id', $editUser->department_id) == $d->id)>{{ $d->name }}</option>
                @endforeach
            </select>
        </div>
        @if(auth()->user()->isAdmin())
            <div class="sed-field">
                <label for="role">Роль <span class="sed-req">*</span></label>
                @if($editUser->role === 'department_head')
                    <input type="hidden" name="role" value="department_head">
                    <p class="sed-input" style="margin:0;padding:0.5rem 0.65rem;background:var(--surface-muted,#f5f5f5);border-radius:4px;">Начальник отдела</p>
                @else
                    <select class="sed-select" id="role" name="role" required>
                        <option value="user" @selected(old('role', $editUser->role) === 'user')>Сотрудник</option>
                        <option value="admin" @selected(old('role', $editUser->role) === 'admin')>Администратор</option>
                    </select>
                @endif
            </div>
        @else
            <input type="hidden" name="role" value="user">
        @endif
        <div class="sed-field">
            <label for="password">Новый пароль</label>
            <input class="sed-input" id="password" name="password" type="password" minlength="8" autocomplete="new-password" placeholder="Оставьте пустым, если не меняете">
        </div>
        <div class="sed-field">
            <label for="password_confirmation">Повтор пароля</label>
            <input class="sed-input" id="password_confirmation" name="password_confirmation" type="password" minlength="8" autocomplete="new-password">
        </div>
        @if(auth()->user()->isAdmin())
            <div class="sed-field">
                <label class="sed-check-row">
                    <input type="hidden" name="is_active" value="0">
                    <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $editUser->is_active))>
                    <span>Активен</span>
                </label>
            </div>
        @endif
        <div style="display:flex;gap:0.5rem;margin-top:1rem;">
            <button type="submit" class="sed-btn sed-btn--primary sed-btn--sm">Сохранить</button>
            <a class="sed-btn sed-btn--ghost sed-btn--sm" href="{{ route('admin.users.index') }}">Отмена</a>
        </div>
    </form>
</div>
@endsection
