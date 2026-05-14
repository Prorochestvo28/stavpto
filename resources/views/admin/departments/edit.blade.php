@extends('layouts.app')

@section('title', 'Отдел — '.$department->name)

@section('content')
<div class="sed-page-header">
    <h1>Отдел: {{ $department->name }}</h1>
    <p><a href="{{ route('admin.departments.index') }}">← К списку отделов</a></p>
</div>

<div class="sed-card" style="max-width:36rem;">
    <form method="post" action="{{ route('admin.departments.update', $department) }}">
        @csrf
        @method('PUT')
        <div class="sed-field">
            <label for="name">Название <span class="sed-req">*</span></label>
            <input class="sed-input" id="name" name="name" required maxlength="255" value="{{ old('name', $department->name) }}">
        </div>
        <div class="sed-field">
            <label for="description">Описание</label>
            <textarea class="sed-textarea" id="description" name="description" rows="3">{{ old('description', $department->description) }}</textarea>
        </div>
        <div class="sed-field">
            <label for="head_user_id">Главный отдела</label>
            <select class="sed-select" id="head_user_id" name="head_user_id">
                <option value="">— не назначен —</option>
                @foreach($users as $u)
                    <option value="{{ $u->id }}" @selected(old('head_user_id', $department->head_user_id) == $u->id)>
                        {{ $u->displayName() }} ({{ $u->email }})
                    </option>
                @endforeach
            </select>
        </div>
        <div style="display:flex;gap:0.5rem;margin-top:1rem;">
            <button type="submit" class="sed-btn sed-btn--primary sed-btn--sm">Сохранить</button>
            <a class="sed-btn sed-btn--ghost sed-btn--sm" href="{{ route('admin.departments.index') }}">Отмена</a>
        </div>
    </form>
</div>
@endsection
