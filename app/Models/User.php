<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Log;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Throwable;

#[Fillable(['name', 'email', 'password'])]
#[Hidden(['password', 'two_factor_secret', 'two_factor_recovery_codes', 'remember_token'])]
class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, TwoFactorAuthenticatable;

    protected ?Throwable $emailVerificationSendException = null;

    /**
     * Send the email verification notification without crashing registration.
     */
    public function sendEmailVerificationNotification(): void
    {
        $this->emailVerificationSendException = null;

        try {
            $this->notify(new VerifyEmail);
        } catch (Throwable $e) {
            $this->emailVerificationSendException = $e;

            Log::warning('email.verification.send_failed', [
                'user_id' => (int) ($this->id ?? 0),
                'email' => (string) ($this->email ?? ''),
                'exception' => $e::class,
                'error' => $e->getMessage(),
            ]);
        }
    }

    public function emailVerificationSendFailed(): bool
    {
        return $this->emailVerificationSendException !== null;
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'two_factor_confirmed_at' => 'datetime',
        ];
    }
}
