<?php

namespace Modules\Deliveries\Listeners;

use App\Models\User;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Modules\Deliveries\Events\UserRequestedToBecomeDriver;
use Modules\Deliveries\Models\DeliveryRequest;

class NotifyAdminAboutDeliveryRequest
{
    /**
     * Create the event listener.
     */
    public function __construct() {}

    /**
     * Handle the event.
     */
    public function handle(UserRequestedToBecomeDriver $event): void
    {
        $admins = User::role('super-admin')->get();
        foreach ($admins as $admin) {
            Notification::make()
                ->title(title: 'new delivey request')
                ->body("New delivery request {$event->request->id} received")
                ->success()
                ->actions([
                    Action::make('View')
                        ->url(route('filament.admin.resources.delivery-requests.view', $event->request->id))
                ])
                ->sendToDatabase($admin);
        }
    }
}
