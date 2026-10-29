<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use Filament\Widgets\ChartWidget;

class OrdersByStatus extends ChartWidget
{
    protected ?string $heading = 'Orders by status';

    protected static ?int $sort = 3;

    protected ?string $maxHeight = '260px';

    protected function getData(): array
    {
        $counts = Order::query()->selectRaw('status, count(*) as total')->groupBy('status')->pluck('total', 'status');

        return [
            'datasets' => [[
                'data' => $counts->values()->all(),
                'backgroundColor' => ['#6366f1', '#8b5cf6', '#06b6d4', '#22c55e', '#f43f5e', '#f59e0b'],
                'borderWidth' => 0,
            ]],
            'labels' => $counts->keys()->map(fn ($status) => ucfirst($status))->all(),
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }

    protected function getOptions(): array
    {
        return ['cutout' => '65%', 'scales' => ['x' => ['display' => false], 'y' => ['display' => false]]];
    }
}
