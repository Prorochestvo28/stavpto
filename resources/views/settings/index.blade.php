@extends('layouts.app')

@section('title', 'Настройки — СЭД СТАВ')

@section('content')
<div class="sed-page-header">
    <p class="sed-muted" style="margin:0 0 0.35rem;">
        <a href="{{ route('documents.index') }}">← Документы</a>
    </p>
    <h1>Настройки</h1>
    <p class="sed-muted" style="margin:0;">Профиль, пароль, электронная подпись и история действий в системе.</p>
</div>

@if (session('profile_status'))
    <p class="sed-muted" style="margin:0 0 0.75rem;color:var(--primary-dark);">{{ session('profile_status') }}</p>
@endif
@if (session('password_status'))
    <p class="sed-muted" style="margin:0 0 0.75rem;color:var(--primary-dark);">{{ session('password_status') }}</p>
@endif
@if (session('signature_status'))
    <p class="sed-muted" style="margin:0 0 0.75rem;color:var(--primary-dark);">{{ session('signature_status') }}</p>
@endif

<div class="sed-settings-stack" style="display:flex;flex-direction:column;gap:1.25rem;max-width:min(56rem,100%);">
    <div class="sed-card">
        <h2 style="margin-top:0;font-size:1.1rem;">Данные профиля</h2>
        <p class="sed-muted" style="margin:0 0 1rem;font-size:0.9rem;line-height:1.45;">Отдел и должность назначаются администратором; изменить их здесь нельзя.</p>
        <form method="post" action="{{ route('settings.profile') }}">
            @csrf
            <div class="sed-field">
                <label for="sed-settings-name">Логин <span class="sed-req" aria-hidden="true">*</span></label>
                <input class="sed-input" type="text" id="sed-settings-name" name="name" required maxlength="255" value="{{ old('name', auth()->user()->name) }}">
                @error('name')
                    <p class="sed-muted" style="margin:0.35rem 0 0;color:#a33;font-size:0.88rem;">{{ $message }}</p>
                @enderror
            </div>
            <div class="sed-field">
                <label for="sed-settings-email">Email <span class="sed-req" aria-hidden="true">*</span></label>
                <input class="sed-input" type="email" id="sed-settings-email" name="email" required value="{{ old('email', auth()->user()->email) }}">
                @error('email')
                    <p class="sed-muted" style="margin:0.35rem 0 0;color:#a33;font-size:0.88rem;">{{ $message }}</p>
                @enderror
            </div>
            <div class="sed-field">
                <label for="sed-settings-full_name">ФИО</label>
                <input class="sed-input" type="text" id="sed-settings-full_name" name="full_name" maxlength="255" value="{{ old('full_name', auth()->user()->full_name) }}">
                @error('full_name')
                    <p class="sed-muted" style="margin:0.35rem 0 0;color:#a33;font-size:0.88rem;">{{ $message }}</p>
                @enderror
            </div>
            <div class="sed-field">
                <label for="sed-settings-phone">Телефон</label>
                <input class="sed-input" type="text" id="sed-settings-phone" name="phone" maxlength="50" value="{{ old('phone', auth()->user()->phone) }}">
                @error('phone')
                    <p class="sed-muted" style="margin:0.35rem 0 0;color:#a33;font-size:0.88rem;">{{ $message }}</p>
                @enderror
            </div>
            <div class="sed-field">
                <span class="sed-muted" style="font-size:0.9rem;">Должность</span>
                <div style="margin-top:0.35rem;padding:0.5rem 0.65rem;border:1px solid var(--border-color);border-radius:var(--radius);background:var(--secondary-color);font-size:0.95rem;">
                    {{ auth()->user()->position ?: '—' }}
                </div>
            </div>
            <div class="sed-field" style="margin-bottom:0;">
                <span class="sed-muted" style="font-size:0.9rem;">Отдел</span>
                <div style="margin-top:0.35rem;padding:0.5rem 0.65rem;border:1px solid var(--border-color);border-radius:var(--radius);background:var(--secondary-color);font-size:0.95rem;">
                    {{ auth()->user()->department?->name ?? '—' }}
                </div>
            </div>
            <div style="margin-top:1rem;">
                <button type="submit" class="sed-btn sed-btn--primary sed-btn--sm">Сохранить профиль</button>
            </div>
        </form>
    </div>

    <div class="sed-card">
        <h2 style="margin-top:0;font-size:1.1rem;">Смена пароля</h2>
        <form method="post" action="{{ route('settings.password') }}">
            @csrf
            <div class="sed-field">
                <label for="sed-settings-current-password">Текущий пароль <span class="sed-req" aria-hidden="true">*</span></label>
                <input class="sed-input" type="password" id="sed-settings-current-password" name="current_password" required autocomplete="current-password">
                @error('current_password')
                    <p class="sed-muted" style="margin:0.35rem 0 0;color:#a33;font-size:0.88rem;">{{ $message }}</p>
                @enderror
            </div>
            <div class="sed-field">
                <label for="sed-settings-new-password">Новый пароль <span class="sed-req" aria-hidden="true">*</span></label>
                <input class="sed-input" type="password" id="sed-settings-new-password" name="new_password" required minlength="8" autocomplete="new-password">
                @error('new_password')
                    <p class="sed-muted" style="margin:0.35rem 0 0;color:#a33;font-size:0.88rem;">{{ $message }}</p>
                @enderror
            </div>
            <div class="sed-field" style="margin-bottom:0;">
                <label for="sed-settings-new-password-confirmation">Повтор нового пароля <span class="sed-req" aria-hidden="true">*</span></label>
                <input class="sed-input" type="password" id="sed-settings-new-password-confirmation" name="new_password_confirmation" required minlength="8" autocomplete="new-password">
            </div>
            <div style="margin-top:1rem;">
                <button type="submit" class="sed-btn sed-btn--primary sed-btn--sm">Сменить пароль</button>
            </div>
        </form>
    </div>

    <div class="sed-card">
        <h2 style="margin-top:0;font-size:1.1rem;">Электронная подпись</h2>
        <p class="sed-muted" style="margin:0 0 1rem;font-size:0.9rem;">Используется при согласовании и отклонении документов.</p>
        @if ($errors->has('signature_pin'))
            <ul class="sed-muted" style="margin:0 0 1rem;padding-left:1.25rem;color:#a33;">
                @foreach ($errors->get('signature_pin') as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        @endif
        <form method="post" action="{{ route('settings.signature') }}">
            @csrf
            <div class="sed-field" style="margin-bottom:0.5rem;">
                <label for="sed-settings-pin">Новая подпись <span class="sed-req" aria-hidden="true">*</span></label>
                <input class="sed-input" type="password" inputmode="numeric" pattern="\d{4,6}" maxlength="6" autocomplete="new-password" id="sed-settings-pin" name="signature_pin" required placeholder="4–6 цифр">
            </div>
            <div class="sed-field" style="margin-bottom:0;">
                <label for="sed-settings-pin2">Повтор <span class="sed-req" aria-hidden="true">*</span></label>
                <input class="sed-input" type="password" inputmode="numeric" pattern="\d{4,6}" maxlength="6" autocomplete="new-password" id="sed-settings-pin2" name="signature_pin_confirmation" required placeholder="Повтор кода">
            </div>
            <div style="margin-top:1rem;">
                <button type="submit" class="sed-btn sed-btn--primary sed-btn--sm">Сохранить подпись</button>
            </div>
        </form>
    </div>

    <div class="sed-card">
        <h2 style="margin-top:0;font-size:1.1rem;">История активности</h2>
        <p class="sed-muted" style="margin:0 0 1rem;font-size:0.9rem;">Журнал действий в системе (по 20 записей на страницу).</p>
        <div class="sed-table-wrap">
            <table class="sed-table">
                <thead>
                    <tr>
                        <th scope="col">Дата и время</th>
                        <th scope="col">Действие</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($activityLogs as $log)
                        <tr>
                            <td>{{ $log->created_at?->format('d.m.Y H:i:s') ?? '—' }}</td>
                            <td>{{ $log->description }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="2" class="sed-muted">Записей пока нет. Действия появятся после работы в системе.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @include('partials.pagination-prev-next', ['paginator' => $activityLogs, 'ariaLabel' => 'Пагинация журнала активности'])
    </div>
</div>
@endsection
