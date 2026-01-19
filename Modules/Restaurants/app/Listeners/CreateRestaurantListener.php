<?php

namespace Modules\Restaurants\Listeners;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Modules\Restaurants\Events\RestaurantApproved;
use Modules\Restaurants\Models\Restaurant;

class CreateRestaurantListener
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
       
        Restaurant::create([
            'owner_id' => $event->owner->id,
            'name' => $event->record->restaurant_name,
            'email' => $event->record->restaurant_email,
            'phone' => $event->record->restaurant_phone,
            'address' => $event->record->address,
        ]);
    }
}
