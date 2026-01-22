<?php

namespace Modules\Restaurants\Listeners;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Modules\Restaurants\Events\RestaurantApproved;
use Modules\Restaurants\Notifications\RestaurantApprovedNotification;


class SendApprovalEmailListener
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
    }

    /**
     * Handle the event.
     */
    public function handle(RestaurantApproved $event): void
    {
        $event->owner->notify(new RestaurantApprovedNotification());
    }
}
