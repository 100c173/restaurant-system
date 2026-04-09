<?php

namespace Modules\Restaurants\Listeners;

use App\Models\Tenant;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Modules\Restaurants\Events\RestaurantApproved;
use Stancl\Tenancy\Database\DatabaseManager;
use Stancl\Tenancy\Facades\Tenancy;
use Str;

class CreateTenantDatabaseListener
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

        $tenant->users()->attach($event->record->customer_id);


    }
}
