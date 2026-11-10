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
            ->view(
                ['html' => 'emails.verify-account', 'text' => 'emails.verify-account-text'],
                [
                    'communityName' => config('mail.from.name') ?: 'CommunityCAD',
                    'verificationUrl' => $url,
                ]
            );
    }
}
