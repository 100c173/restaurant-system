<?php

namespace Modules\Deliveries\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Modules\Deliveries\Models\DeliveryRequest;

class DeliveryRequestStatusUpdated extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(protected DeliveryRequest $delivery_request) {}

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
        $delivery_request = $this->delivery_request;

        $message = (new MailMessage)
            ->subject('Your delivery_request Status has been updated')
            ->greeting('Hello ' . $notifiable->name . ',')
            ->line('Your delivery_request ' . ' status has been updated to: **' . ucfirst($delivery_request->status) . '**.');

        if ($delivery_request->status === 'rejected' && $delivery_request->cancel_reason) {
            $message->line('Reason for cancellation: ' . $delivery_request->cancel_reason);
        }

        if ($delivery_request->status === 'approved' && $delivery_request->preparation_time) {
            $message->line($delivery_request->admin_note);
        }

        return $message->line('Thank you for using our service!');
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray($notifiable): array
    {
        return [];
    }
}
