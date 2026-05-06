<?php

namespace App\Filament\Resources\Subscriptions\Widgets;

use App\Models\Subscription;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
class SubscriptionStatsWidget extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        $expiringSoon = Subscription::query()
            ->where('status', 'active')
            ->where('ends_at', '<=', now()->addDays(7))
            ->where('ends_at', '>=', now())
            ->count();

        $trialExpiring = Subscription::query()
            ->where('status', 'trial')
            ->where('trial_ends_at', '<=', now()->addDays(7))
            ->where('trial_ends_at', '>=', now())
            ->count();

        return [
            Stat::make('Active', Subscription::where('status', 'active')->count())
                ->color('success')
                ->icon('heroicon-o-check-circle'),

            Stat::make('Trial', Subscription::where('status', 'trial')->count())
                ->color('info')
                ->icon('heroicon-o-beaker'),

            Stat::make('Past due', Subscription::where('status', 'past_due')->count())
                ->color('warning')
                ->icon('heroicon-o-exclamation-triangle'),

            Stat::make('Expiring in 7 days', $expiringSoon)
                ->color($expiringSoon > 0 ? 'warning' : 'gray')
                ->icon('heroicon-o-clock')
                ->description('Active subscriptions'),

            Stat::make('Trial ending in 7 days', $trialExpiring)
                ->color($trialExpiring > 0 ? 'info' : 'gray')
                ->icon('heroicon-o-clock')
                ->description('Trial accounts'),

            Stat::make('Cancelled', Subscription::where('status', 'cancelled')->count())
                ->color('danger')
                ->icon('heroicon-o-x-circle'),
        ];
    }
}