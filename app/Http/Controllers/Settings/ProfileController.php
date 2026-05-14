<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ProfileController extends Controller
{
    public function update(Request $request): RedirectResponse
    {
        $user = $request->user();

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'full_name' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
        ]);

        $user->fill([
            'name' => $data['name'],
            'email' => $data['email'],
            'full_name' => $data['full_name'] ?? null,
            'phone' => $data['phone'] ?? null,
        ]);
        $user->save();

        return redirect()
            ->route('settings.index')
            ->with('profile_status', 'Данные профиля сохранены.');
    }
}
