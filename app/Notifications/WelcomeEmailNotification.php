<?php

declare(strict_types=1);

namespace App\Notifications;

use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\URL;

class WelcomeEmailNotification extends VerifyEmail
{
    use Queueable;

    /**
     * Get the mail representation of the notification.
     */
    public function toMail($notifiable): MailMessage
    {
        $verificationUrl = $this->buildFrontendVerificationUrl($notifiable);

        return (new MailMessage())
            ->subject('Welcome to ' . config('app.name') . '!')
            ->greeting('Welcome ' . $notifiable->name . '!')
            ->line('Thank you for registering with ' . config('app.name') . '.')
            ->line('Please verify your email address to get started.')
            ->action('Verify Email Address', $verificationUrl)
            ->line('This verification link will expire in 60 minutes.')
            ->line('If you did not create an account, no further action is required.')
            ->salutation('Regards, ' . config('app.name') . ' Team');
    }

    /**
     * Build the frontend verification URL that will call the API
     */
    protected function buildFrontendVerificationUrl($notifiable): string
    {
        // Generate the signed API URL
        $apiUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            [
                'id' => $notifiable->getKey(),
                'hash' => sha1($notifiable->getEmailForVerification()),
            ]
        );

        // Extract query parameters from API URL
        $parsedUrl = parse_url($apiUrl);
        parse_str($parsedUrl['query'] ?? '', $queryParams);

        // Build frontend URL with all necessary parameters
        $frontendBaseUrl = config('app.url');
        
        return $frontendBaseUrl . '/verify-email?' . http_build_query([
            'id' => $notifiable->getKey(),
            'hash' => sha1($notifiable->getEmailForVerification()),
            'expires' => $queryParams['expires'] ?? '',
            'signature' => $queryParams['signature'] ?? '',
        ]);
    }
}

