<?php

namespace Modules\Deliveries\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Modules\Deliveries\Events\DeliveryRequestStatusChanged;
use Modules\Deliveries\Events\UserRequestedToBecomeDriver;
use Modules\Deliveries\Listeners\HandleDeliveryStatusChange;
use Modules\Deliveries\Listeners\NotifyAdminAboutDeliveryRequest;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event handler mappings for the application.
     *
     * @var array<string, array<int, string>>
     */
    protected $listen = [
        UserRequestedToBecomeDriver::class => [
            NotifyAdminAboutDeliveryRequest::class,
        ],
        DeliveryRequestStatusChanged::class =>[
            HandleDeliveryStatusChange::class,
        ]
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
    protected function configureEmailVerification(): void {}
}
