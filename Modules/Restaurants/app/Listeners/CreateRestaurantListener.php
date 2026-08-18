<?php

namespace Modules\Restaurants\Listeners;

use App\Models\Plan;
use App\Models\Subscription;
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
            'phone' => $event->record->restaurant_phone,
            'latitude' => $event->record->latitude,
            'longitude' => $event->record->longitude,
            'is_active' => false,
        ]);

        $restaurant->categories()->sync($event->record->categories ?? []);

        $plan = Plan::where('code','FREE')->firstOrFail() ;
        Subscription::create([
            'tenant_id'        => $tenant->id,
            'plan_id'          => $plan->id ,
            'price'            => $plan->price ,
            'billing_interval' => $plan->billing_interval,
            'status'           => 'active',
            'starts_at'        => now(),

        ]);
        $event->record->update([
            'status' => 'approved',
        ]);
    }
}
