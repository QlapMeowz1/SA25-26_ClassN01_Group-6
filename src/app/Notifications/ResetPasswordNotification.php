<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ResetPasswordNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(private readonly string $token)
    {
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $expiresIn = (int) config('auth.passwords.users.expire', 15);
        $resetUrl = route('password.reset', [
            'token' => $this->token,
            'email' => $notifiable->getEmailForPasswordReset(),
        ]);

        return (new MailMessage)
            ->subject('Reset your BadNet password')
            ->greeting('Hello ' . ($notifiable->name ?? 'player') . ',')
            ->line('We received a request to reset the password for your BadNet account.')
            ->action('Reset Password', $resetUrl)
            ->line("This reset link expires in {$expiresIn} minutes and can only be used once.")
            ->line('For your security, do not share this email or reset link with anyone.')
            ->line('If you did not request a password reset, you can safely ignore this email. Your password will remain unchanged.')
            ->salutation('BadNet Security Team');
    }
}
