<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules\Password;

class PasswordController extends Controller
{
    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'current_password' => ['required', 'current_password'],
            'new_password' => ['required', 'string', Password::min(8), 'confirmed'],
        ], [], [
            'current_password' => 'текущий пароль',
            'new_password' => 'новый пароль',
        ]);

        $user = $request->user();
        $user->password = $data['new_password'];
        $user->save();

        return redirect()
            ->route('settings.index')
            ->with('password_status', 'Пароль успешно изменён.');
    }
}
