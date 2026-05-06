<?php

namespace App\Filament\Resources\Subscriptions\Pages;

use App\Filament\Resources\Subscriptions\SubscriptionResource;
use App\Models\Plan;
use Filament\Resources\Pages\CreateRecord;

class CreateSubscription extends CreateRecord
{
    protected static string $resource = SubscriptionResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    // Ensure price + interval are always sourced from plan, never stale
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $plan = Plan::find($data['plan_id']);

        if ($plan) {
            $data['price']            = $plan->price;
            $data['billing_interval'] = $plan->billing_interval;
        }

        // Default trial_ends_at to 30 days if status is trial and not set
        if (($data['status'] ?? 'trial') === 'trial' && empty($data['trial_ends_at'])) {
            $data['trial_ends_at'] = now()->addDays(30);
        }

        return $data;
    }
}