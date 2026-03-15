<?php

namespace Modules\Restaurants\Listeners;

use App\Models\User;
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
        $owner = User::find($event->record->customer_id);
        $owner->notify(new RestaurantApprovedNotification());
    }
}
