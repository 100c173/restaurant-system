<?php

namespace Modules\Deliveries\Listeners;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Modules\Deliveries\Models\Delivery;

class HandleDeliveryStatusChange
{
    /**
     * Create the event listener.
     */
    public function __construct() {}

    /**
     * Handle the event.
     */
    public function handle($event): void
    {
        $deliveryRequest = $event->deliveryRequest;
        $user = $deliveryRequest->customer;

        if ($deliveryRequest->status === 'approved') {

            $user->assignRole('delivery');


            Delivery::firstOrCreate([
                'user_id' => $user->id,
            ], [
                'vehicle_type' => $deliveryRequest->vehicle_type,
                'vehicle_number' =>$deliveryRequest->vehicle_number,
                'status' => 'available',
            ]);
        }
    }
}
