<?php

namespace App\Support;

use Illuminate\Support\ViewErrorBag;

class ToastMessages
{
    /**
     * @return list<array{type: string, message: string}>
     */
    public static function collect(): array
    {
        $toasts = [];

        foreach (['status', 'profile_status', 'password_status', 'signature_status', 'comment_status'] as $key) {
            $message = session($key);
            if (is_string($message) && $message !== '') {
                $toasts[] = ['type' => 'success', 'message' => $message];
            }
        }

        $errors = session('errors');
        if ($errors instanceof ViewErrorBag) {
            foreach ($errors->all() as $message) {
                if (is_string($message) && $message !== '') {
                    $toasts[] = ['type' => 'error', 'message' => $message];
                }
            }
        }

        return $toasts;
    }
}
