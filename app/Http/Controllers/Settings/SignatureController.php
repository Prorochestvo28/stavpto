<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class SignatureController extends Controller
{
    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'signature_pin' => ['required', 'string', 'regex:/^\d{4,6}$/', 'confirmed'],
        ], [
            'signature_pin.regex' => 'Электронная подпись — от 4 до 6 цифр.',
        ]);

        $request->user()->signature_pin = $data['signature_pin'];
        $request->user()->save();

        return redirect()
            ->route('settings.index')
            ->with('signature_status', 'Электронная подпись сохранена. Теперь доступны действия согласования.');
    }
}
