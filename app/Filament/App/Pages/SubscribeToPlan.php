<?php
// app/Filament/Tenant/Pages/SubscribeToPlan.php
namespace App\Filament\App\Pages;

use App\Models\Plan;
use App\Models\ShamCashAccount;
use App\Models\Subscription;
use BackedEnum;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Validate;

class SubscribeToPlan extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCreditCard;
    protected string $view = 'filament.tenant.pages.subscribe-to-plan';
    protected static bool $shouldRegisterNavigation = false;

    // Plan ID passed as query param ?plan=x

    public int $planId;

    #[Validate('required|min:4|max:100')]
    public string $transactionNumber = '';

    public function mount(): void
    {
        $planId = request()->query('plan');

        abort_unless($planId, 404);

        $this->planId = (int) $planId;

        abort_unless(
            Plan::active()->where('id', $this->planId)->exists(),
            404
        );
    }

    #[Computed]
    public function plan(): Plan
    {
        return Plan::active()
            ->with(['features' => fn($q) => $q->orderBy('name')])
            ->findOrFail($this->planId);
    }

    #[Computed]
    public function shamCashAccount(): ?ShamCashAccount
    {
        return ShamCashAccount::first();
    }

    public function getTitle(): string
    {
        return 'Subscribe to ' . $this->plan->name;
    }

    public function submit(): void
    {
        $this->validate();

        Subscription::create([
            'tenant_id' => tenant('id'),
            'plan_id' => $this->planId,
            'price' => $this->plan->price,
            'billing_interval' => $this->plan->billing_interval,
            'status' => 'pending',
            'payment_reference' => $this->transactionNumber,
        ]);

        // Flash to session so the plans page can show the banner
        session()->put('subscription_submitted', true);
        session()->put('subscription_plan_name', $this->plan->name);

        $this->redirect(SubscriptionPlans::getUrl());
    }

    public function back(): void
    {
        $this->redirect(SubscriptionPlans::getUrl());
    }
}