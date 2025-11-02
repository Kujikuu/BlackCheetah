<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewFranchiseeCredentials extends Notification
{
    use Queueable;

    public string $password;

    public string $unitCode;

    public string $loginUrl;

    /**
     * Create a new notification instance.
     */
    public function __construct(string $password, string $unitCode, string $loginUrl)
    {
        $this->password = $password;
        $this->unitCode = $unitCode;
        $this->loginUrl = $loginUrl;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Welcome to Cheetah - Your Franchisee Account')
            ->greeting('Welcome to Cheetah!')
            ->line('Your franchisee account has been created successfully.')
            ->line('**Unit Code:** '.$this->unitCode)
            ->line('**Email:** '.$notifiable->email)
            ->line('**Temporary Password:** '.$this->password)
            ->line('Please use these credentials to log in to your account. You will be prompted to complete your profile information on your first login.')
            ->action('Login to Your Account', $this->loginUrl)
            ->line('For security reasons, please change your password after your first login.')
            ->line('If you have any questions, please contact our support team.')
            ->line('Thank you for joining Cheetah!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'unit_code' => $this->unitCode,
            'login_url' => $this->loginUrl,
        ];
    }
}
