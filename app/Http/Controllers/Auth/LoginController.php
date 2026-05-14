<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserActivityLog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class LoginController extends Controller
{
    public function create(): View
    {
        return view('auth.login');
    }

    public function store(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ]);

        $user = User::query()->where('email', $credentials['email'])->first();
        if (! $user || ! $user->is_active) {
            return back()->withErrors(['email' => 'Учётная запись не найдена или отключена.'])->onlyInput('email');
        }

        if (! Auth::attempt(['email' => $credentials['email'], 'password' => $credentials['password']], $request->boolean('remember'))) {
            return back()->withErrors(['email' => 'Неверный пароль.'])->onlyInput('email');
        }

        $request->session()->regenerate();

        UserActivityLog::query()->create([
            'user_id' => Auth::id(),
            'description' => 'Вход в систему',
            'route_name' => 'login.store',
            'http_method' => 'POST',
        ]);

        return redirect()->intended(route('documents.index'));
    }
}
