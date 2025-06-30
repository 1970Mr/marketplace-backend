<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class InstagramTokenRefreshFailed extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(protected \Throwable $exception)
    {
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
            ->error()
            ->subject('CRITICAL: Failed to Refresh Instagram CSRF Token')
            ->greeting('Hello Admin,')
            ->line('The scheduled job to refresh the Instagram CSRF token has failed. This requires immediate attention to prevent service disruption.')
            ->line('**Error Message:** ' . $this->exception->getMessage())
            ->line('**File:** ' . $this->exception->getFile())
            ->line('**Line:** ' . $this->exception->getLine())
            ->line('Please investigate the issue with the ProxyService or the connection to Instagram.');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }
}
