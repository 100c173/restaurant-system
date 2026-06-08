<?php

namespace Modules\Orders\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Modules\Orders\Models\Order;
use Filament\Notifications\Notification as FilamentNotification;


class NewOrderOwnerNotification extends Notification
{
    use Queueable;

    public function __construct(private Order $order) {}

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toDatabase($notifiable): array
    {
        return array_merge(
            FilamentNotification::make()
                ->title('لديك طلب جديد')
                ->body("وصلك طلب جديد برقم #{$this->order->reference_number} بقيمة {$this->order->total}")
                ->icon('heroicon-o-bell-alert')
                ->iconColor('warning')
                ->getDatabaseMessage(),
            ['tenant_id' => $this->order->tenant_id]
        );
    }
}
