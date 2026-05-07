<?php

namespace App\Filament\App\Pages;

use App\Models\Plan;
use App\Models\Subscription;
use BackedEnum;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Livewire\Attributes\Computed;

class SubscriptionPlans extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCreditCard;
    protected static ?string $navigationLabel = 'Subscription';
    protected static ?string $title = 'Subscription Plans';
    protected string $view = 'filament.tenant.pages.subscription-plans';

    // Flash banner state — populated from session on mount
    public bool $justSubmitted = false;
    public string $submittedPlanName = '';

    public function mount(): void
    {
        if (session()->pull('subscription_submitted')) {
            $this->justSubmitted = true;
            $this->submittedPlanName = session()->pull('subscription_plan_name', '');
        }
    }

    #[Computed]
    public function plans()
    {
        return Plan::active()
            ->with(['features' => fn($q) => $q->orderBy('name')])
            ->orderBy('price')
            ->get();
    }

    #[Computed]
    public function currentSubscription(): ?Subscription
    {
        return Subscription::where('tenant_id', tenant('id'))
            ->whereIn('status', ['active', 'trial'])
            ->with('plan')
            ->latest()
            ->first();
    }

    public function subscribe(int $planId): void
    {
        $this->redirect(
            SubscribeToPlan::getUrl() . '?plan=' . $planId
        );
    }
}