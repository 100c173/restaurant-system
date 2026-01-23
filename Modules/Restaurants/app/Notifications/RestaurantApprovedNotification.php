<?php

namespace Modules\Restaurants\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class RestaurantApprovedNotification extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct()
    {
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via($notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('🎉 Congratulations! Your Restaurant Has Been Approved')
            ->greeting("Hello {$notifiable->name}!")
            ->line('You have been accepted as a tenant on our application.')
            ->line('An account has been created for you. Set password, and then you can login .')
            ->line('**Now you can:**')
            ->line('- Add your restaurant\'s information')
            ->line('- Manage your restaurant')
            ->line('- Monitor your activity')
            ->line('- Easily reach your customers')
            ->line('Thank you for joining us!');
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray($notifiable): array
    {
        return [
            'type' => 'restaurant_approved',
            'message' => 'Your restaurant has been approved!',
            'user_id' => $notifiable->id,
        ];
    }
}
