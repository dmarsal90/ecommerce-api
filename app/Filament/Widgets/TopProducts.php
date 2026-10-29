<?php

namespace App\Filament\Widgets;

use App\Models\OrderItem;
use Filament\Widgets\ChartWidget;

class TopProducts extends ChartWidget
{
    protected ?string $heading = 'Top products by revenue';

    protected static ?int $sort = 4;

    protected ?string $maxHeight = '260px';

    protected function getData(): array
    {
        $top = OrderItem::query()
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->where('orders.status', '!=', 'cancelled')
            ->selectRaw('order_items.product_name, sum(order_items.total) as revenue')
            ->groupBy('order_items.product_name')
            ->orderByDesc('revenue')
            ->limit(5)
            ->pluck('revenue', 'product_name');

        return [
            'datasets' => [[
                'label' => 'Revenue ($)',
                'data' => $top->map(fn ($revenue) => round($revenue, 2))->values()->all(),
                'backgroundColor' => '#6366f1',
                'borderRadius' => 6,
            ]],
            'labels' => $top->keys()->map(fn ($name) => str($name)->limit(24)->toString())->all(),
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getOptions(): array
    {
        return ['indexAxis' => 'y', 'plugins' => ['legend' => ['display' => false]]];
    }
}
