<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use Filament\Widgets\ChartWidget;

class RevenueByPaymentMethod extends ChartWidget
{
    protected ?string $heading = 'Revenue by payment method';

    protected static ?int $sort = 5;

    protected ?string $maxHeight = '260px';

    protected function getData(): array
    {
        $revenue = Order::where('payment_status', 'paid')
            ->selectRaw('payment_method, sum(total) as revenue')
            ->groupBy('payment_method')
            ->pluck('revenue', 'payment_method');

        return [
            'datasets' => [[
                'data' => $revenue->map(fn ($total) => round($total, 2))->values()->all(),
                'backgroundColor' => ['#22c55e', '#6366f1', '#f59e0b', '#06b6d4'],
                'borderWidth' => 0,
            ]],
            'labels' => $revenue->keys()->map(fn ($method) => str($method)->replace('_', ' ')->title()->toString())->all(),
        ];
    }

    protected function getType(): string
    {
        return 'pie';
    }

    protected function getOptions(): array
    {
        return ['scales' => ['x' => ['display' => false], 'y' => ['display' => false]]];
    }
}
