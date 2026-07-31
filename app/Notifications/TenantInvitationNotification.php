<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TenantInvitationNotification extends Notification
{
    use Queueable;

    public function __construct(
        private readonly string $tenantName,
        private readonly string $inviteUrl,
        private readonly ?string $expiresAt,
    ) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $mail = (new MailMessage)
            ->subject('Undangan akses OptiBus')
            ->greeting('Halo,')
            ->line("Anda diundang untuk bergabung ke tenant {$this->tenantName} di OptiBus.")
            ->line('Klik tombol di bawah ini dan login menggunakan akun Google dengan email yang menerima undangan ini.')
            ->action('Login dengan Google', $this->inviteUrl);

        if ($this->expiresAt) {
            $mail->line("Undangan ini berlaku sampai {$this->expiresAt}.");
        }

        return $mail
            ->line('Jika Anda tidak mengenal undangan ini, abaikan email ini.')
            ->salutation('Salam, Tim OptiBus');
    }
}
