<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Log;

class SendSafeEmailVerificationNotification
{
    public function handle(Registered $event): void
    {
        $user = $event->user;
        if (! $user || $user->hasVerifiedEmail()) {
            return;
        }

        try {
            $user->sendEmailVerificationNotification();
        } catch (\Throwable $e) {
            Log::warning('email.verification.send_failed', [
                'user_id' => (int) ($user->id ?? 0),
                'email' => (string) ($user->email ?? ''),
                'exception' => $e::class,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
