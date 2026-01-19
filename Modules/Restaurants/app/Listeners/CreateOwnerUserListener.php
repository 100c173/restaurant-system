<?php

namespace Modules\Restaurants\Listeners;

use App\Models\User;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Hash;
use Modules\Restaurants\Events\RestaurantApproved;
use Str;

class CreateOwnerUserListener
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
        $user = User::where('email', $event->record->owner_email)->first();
       
        if (!$user) {
            $user = User::create([
                'name' => $event->record->owner_name,
                'email' => $event->record->owner_email,
                'phone' => $event->record->owner_phone,
                'password' => Hash::make(Str::random(32)),
                'password_set_at' => null,
            ]);
            $event->isNewUser = true;
        }
        $event->owner=$user;
    }
}
