<?php

namespace Modules\Restaurants\Listeners;

use App\Models\User;
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
        $user = User::find($event->record->customer_id);
        $user->assignRole('restaurant-owner');

        $restaurant = Restaurant::create([
            'owner_id' => $user->id,
            'name' => $event->record->restaurant_name,
            'address' => $event->record->address,
            'latitude' => $event->record->latitude,
            'longitude' => $event->record->longitude,
        ]);

        $restaurant->categories()->sync($event->record->categories ?? []);
        $event->record->update([
            'status' => 'approved',
        ]);
    }
}
