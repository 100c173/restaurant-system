<?php

namespace Modules\Orders\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Modules\Orders\Models\Order;

class OrderStatusUpdated extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(public Order $order) {}

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
        $order = $this->order;

        $message = (new MailMessage)
            ->subject('Your Order Status has been updated')
            ->greeting('Hello ' . $notifiable->name . ',')
            ->line('Your order #' . $order->order_number . ' status has been updated to: **' . ucfirst($order->status) . '**.');

        if ($order->status === 'cancelled' && $order->cancel_reason) {
            $message->line('Reason for cancellation: ' . $order->cancel_reason);
        }

        if ($order->status === 'accepted' && $order->preparation_time) {
            $message->line('Preparation time take: ' . $order->preparation_time);
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
