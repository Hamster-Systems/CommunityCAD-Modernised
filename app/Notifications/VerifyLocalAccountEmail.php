<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\URL;

class VerifyLocalAccountEmail extends Notification
{
    use Queueable;

    public function __construct(private int $userId, private string $email)
    {
    }

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        $url = URL::temporarySignedRoute('auth.local.verify', now()->addHours(24), [
            'user' => $this->userId,
            'hash' => sha1(strtolower($this->email)),
        ]);

        return (new MailMessage)
            ->subject('Verify your CommunityCAD email')
            ->line('Confirm your email address before signing in with your CommunityCAD password.')
            ->action('Verify email address', $url)
            ->line('This verification link expires after 24 hours. If you did not register, you can ignore this message.');
    }
}
