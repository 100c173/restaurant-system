<?php

namespace Modules\Restaurants\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Modules\Restaurants\Events\RestaurantApproved;
use Modules\Restaurants\Listeners\CreateRestaurantListener;
use Modules\Restaurants\Listeners\SendApprovalEmailListener;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event handler mappings for the application.
     *
     * @var array<string, array<int, string>>
     */
    protected $listen = [
        RestaurantApproved::class => [
            CreateRestaurantListener::class,
            SendApprovalEmailListener::class,
        ],
    ];

    /**
     * Indicates if events should be discovered.
     *
     * @var bool
     */
    protected static $shouldDiscoverEvents = true;

    /**
     * Configure the proper event listeners for email verification.
     */
    protected function configureEmailVerification(): void
    {
    }
}
