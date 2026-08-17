<?php
namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;
use Modules\Orders\Models\Order;

class OrderChart extends ChartWidget
{
    protected static ?int $sort = 2;
    protected ?string $heading = 'Orders - Last 7 Days';

    protected function getData(): array
    {
        $orders = Order::query()
            ->where('created_at', '>=', now()->subDays(6)->startOfDay())
            ->selectRaw('DATE(created_at) as date, COUNT(*) as total')
            ->groupBy('date')
            ->orderBy('date')
            ->pluck('total', 'date');

        $labels = [];
        $data   = [];

        for ($i = 6; $i >= 0; $i--) {

            $date = now()->subDays($i);

            $dateKey = $date->format('Y-m-d');

            $labels[] = $date->format('D');

            $data[] = $orders->get($dateKey, 0);
        }
        return [
            'datasets' => [
                [
                    'label' => 'Orders',
                    'data'  => $data,
                ],
            ],

            'labels'   => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
