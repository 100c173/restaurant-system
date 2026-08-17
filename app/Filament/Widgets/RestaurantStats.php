<?php

namespace App\Filament\Widgets;

use App\Models\User;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;
use Modules\Orders\Models\Order;
use Modules\Restaurants\Models\Restaurant;

class RestaurantStats extends StatsOverviewWidget
{
    protected static ?int $sort = 1;
    protected function getStats(): array
    {
        return [

            Stat::make(
                'Restaurants',
                number_format(Restaurant::count())
            )
                ->description('Registered restaurants')
                ->descriptionIcon('heroicon-m-building-storefront')
                ->color('primary')
                ->chart(
                    $this->getDailyCount(Restaurant::class)
                ),

            Stat::make(
                'Users',
                number_format(User::count())
            )
                ->description('Registered users')
                ->descriptionIcon('heroicon-m-users')
                ->color('info')
                ->chart(
                    $this->getDailyCount(User::class)
                ),

            Stat::make(
                'Total Orders',
                number_format(Order::count())
            )
                ->description('All orders')
                ->descriptionIcon('heroicon-m-shopping-bag')
                ->color('success')
                ->chart(
                    $this->getDailyCount(Order::class)
                ),

            Stat::make(
                'Pending Orders',
                number_format(
                    Order::where('status', 'pending')->count()
                )
            )
                ->description('Orders waiting for processing')
                ->descriptionIcon('heroicon-m-clock')
                ->color('warning')
                ->chart(
                    $this->getDailyCount(
                        Order::class,
                        'pending'
                    )
                ),

            Stat::make(
                'Completed Orders',
                number_format(
                    Order::where('status', 'completed')->count()
                )
            )
                ->description('Successfully completed orders')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success')
                ->chart(
                    $this->getDailyCount(
                        Order::class,
                        'completed'
                    )
                ),

            Stat::make(
                'Revenue',
                number_format(Order::sum('total'), 2) . ' SYP'
            )
                ->description('Total revenue')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('success')
                ->chart(
                    $this->getDailyRevenue()
                ),
        ];
    }

    private function getDailyCount(
        string $model,
        ?string $status = null
    ): array {
        $query = $model::query()
            ->where(
                'created_at',
                '>=',
                now()->subDays(6)->startOfDay()
            );

        if ($status) {
            $query->where('status', $status);
        }

        $results = $query
            ->selectRaw('DATE(created_at) as date, COUNT(*) as total')
            ->groupBy('date')
            ->orderBy('date')
            ->pluck('total', 'date');

        return $this->fillLastSevenDays($results);
    }

    private function getDailyRevenue(): array
    {
        $results = Order::query()
            ->where(
                'created_at',
                '>=',
                now()->subDays(6)->startOfDay()
            )
            ->selectRaw(
                'DATE(created_at) as date, SUM(total) as total'
            )
            ->groupBy('date')
            ->orderBy('date')
            ->pluck('total', 'date');

        return $this->fillLastSevenDays($results);
    }

    private function fillLastSevenDays($results): array
    {
        $data = [];

        for ($i = 6; $i >= 0; $i--) {
            $date = now()
                ->subDays($i)
                ->format('Y-m-d');

            $data[] = (float) ($results[$date] ?? 0);
        }

        return $data;
    }
}
