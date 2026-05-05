<?php

namespace Modules\Restaurants\Listeners;

use App\Models\Tenant;
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
        $tenant = Tenant::create([
            //'id' => $event->record->restaurant_name,
            'owner_id' => $event->record->customer_id,
            'name' => $event->record->restaurant_name,
        ]);

        $tenant->createDomain([
            'domain' => $event->record->restaurant_name . '.localhost',
            'tenant_id' => $tenant->id,
        ]);

        $user = User::find($event->record->customer_id);
        $user->assignRole('restaurant-owner');

        $restaurant = Restaurant::create([
            'tenant_id' => $tenant->id,
            'name' => $event->record->restaurant_name,
            'address' => $event->record->address,
            'description' => $event->record->description,
            'phone' => $event->record->phone,
            'latitude' => $event->record->latitude,
            'longitude' => $event->record->longitude,
        ]);

        $restaurant->categories()->sync($event->record->categories ?? []);
        $event->record->update([
            'status' => 'approved',
        ]);
    }
}
