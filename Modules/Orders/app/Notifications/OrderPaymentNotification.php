<?php

namespace Modules\Orders\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Modules\Orders\Models\Order;
use Filament\Notifications\Notification as FilamentNotification;


class OrderPaymentNotification extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        private String $reference_number ,
        private String $total,
        private String $tenant_id,
    ){}
    

    /**
     * Get the notification's delivery channels.
     */
    public function via($notifiable): array
    {
        return ['database'];
    }


    public function toDatabase($notifiable): array
    {
        return array_merge(
            FilamentNotification::make()
                ->title('إيصال دفع')
                ->body("وصلك الدفع لطلب  برقم #{$this->reference_number} بقيمة {$this->total}")
                ->icon('heroicon-o-bell-alert')
                ->iconColor('warning')
                ->getDatabaseMessage(),
            ['tenant_id' => $this->tenant_id]
        );
    }

}
