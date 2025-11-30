<?php

namespace Modules\Orders\Listeners;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Modules\Orders\Events\OrderPlaced;
use Modules\Orders\Notifications\NewOrderNotification;

class NotifyRestaurantOwner
{
    /**
     * Create the event listener.
     */
    public function __construct() {}

    /**
     * Handle the event.
     */
    public function handle(OrderPlaced $event): void
    {
        $owner = $event->order->restaurant->owner;

        //Laravel notification for (email , phone)
        $owner->notify(new NewOrderNotification($event->order));

        //Filament Notification (UI)
        \Filament\Notifications\Notification::make()
            ->title(title: 'new order')
            ->body("New order number {$event->order->id} received")
            ->success()
            ->sendToDatabase($owner);
    }

    //LinkedIn : Amer Oniza
}
