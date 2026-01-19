<?php

namespace Modules\Restaurants\Listeners;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Modules\Restaurants\Events\RestaurantApproved;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Mail;


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
   

        $user = $event->owner;

        $emailBody = "Hello {$user->name},\n\n" . "Your restaurant's request has been approved.\n";

        Mail::raw($emailBody, function ($message) use ($user) {
            $message->to($user->email)
                ->subject("The restaurant's request has been accepted");
        });
    }
}
