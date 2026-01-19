<?php

namespace Modules\Restaurants\Listeners;

use App\Models\Tenant;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Artisan;
use Modules\Restaurants\Events\RestaurantApproved;
use Stancl\Tenancy\Database\DatabaseManager;
use Stancl\Tenancy\Facades\Tenancy;

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
            'id' => $event->record->id,
            'data' => [
                'restaurant_id' => $event->record->id,
            ],
        ]);

        $tenant->createDomain([
            'domain' => $event->record->restaurant_name . '.myapp.com',
            'tenant_id' => $tenant->id,
        ]);

        Artisan::call('tenants:migrate');
        

    }
}
