<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class SignatureController extends Controller
{
    public function update(Request $request): RedirectResponse
    {
        $user = $request->user();
        $hasPin = $user->hasSignaturePin();

        $pinRule = ['required', 'string', 'regex:/^\d{4,6}$/'];

        $rules = [
            'signature_pin' => [...$pinRule, 'confirmed'],
        ];

        if ($hasPin) {
            $rules['current_signature_pin'] = $pinRule;
        }

        $data = $request->validate($rules, [
            'signature_pin.regex' => 'Электронная подпись — от 4 до 6 цифр.',
            'current_signature_pin.regex' => 'Текущая подпись — от 4 до 6 цифр.',
        ], [
            'current_signature_pin' => 'текущая подпись',
            'signature_pin' => 'новая подпись',
        ]);

        if ($hasPin && ! $user->verifySignaturePin($data['current_signature_pin'])) {
            throw ValidationException::withMessages([
                'current_signature_pin' => ['Неверный текущий код подписи.'],
            ]);
        }

        $user->signature_pin = $data['signature_pin'];
        $user->save();

        $status = $hasPin
            ? 'Электронная подпись изменена.'
            : 'Электронная подпись сохранена. Теперь доступны действия согласования.';

        return redirect()
            ->route('settings.index')
            ->with('signature_status', $status);
    }
}
