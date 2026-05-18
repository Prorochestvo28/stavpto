@extends('layouts.guest')

@section('title', 'Вход — СЭД СТАВ')

@section('content')
<div class="sed-login-page">
    <div class="sed-login-card">
        <div class="sed-login-card__logo">
            <img src="{{ asset('img/logo.svg') }}" alt="СЭД СТАВ" width="119" height="48">
        </div>
        <h1>Вход в систему</h1>

        <form method="post" action="{{ route('login.store') }}">
            @csrf
            <div class="sed-field">
                <label for="email">Электронная почта <span class="sed-req" aria-hidden="true">*</span></label>
                <input class="sed-input" type="email" id="email" name="email" value="{{ old('email') }}" required autocomplete="username" placeholder="email@company.ru">
            </div>
            <div class="sed-field">
                <label for="password">Пароль <span class="sed-req" aria-hidden="true">*</span></label>
                <input class="sed-input" type="password" id="password" name="password" required autocomplete="current-password" placeholder="Пароль">
            </div>
            <div class="sed-field" style="margin-bottom:0;">
            </div>
            <button type="submit" class="sed-btn sed-btn--primary" style="width:100%;margin-top:0.75rem;box-sizing:border-box;">Войти</button>
        </form>
    </div>
</div>
@endsection
