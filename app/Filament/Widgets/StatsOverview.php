<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $todayStart = now()->startOfDay();
        $todayEnd = now()->endOfDay();

        $salesToday = Order::where('payment_status', 'paid')
            ->whereBetween('paid_at', [$todayStart, $todayEnd])
            ->sum('total');

        $ordersToday = Order::whereBetween('created_at', [$todayStart, $todayEnd])->count();

        $customersToday = User::whereBetween('created_at', [$todayStart, $todayEnd])->count();

        $lowStockCount = Product::where('track_quantity', true)
            ->whereRaw('quantity <= low_stock_threshold')
            ->count();

        return [
            Stat::make('Sales Today', '$' . number_format($salesToday, 2))
                ->description('Orders: ' . $ordersToday)
                ->color('success')
                ->chart([12, 19, 3, 5, 2, 3, 8, 12, 15, 18, 20, 25]),

            Stat::make('Orders Today', $ordersToday)
                ->description('Total orders placed today')
                ->color('primary')
                ->chart([5, 8, 3, 12, 7, 9, 15, 18, 22, 25, 28, 32]),

            Stat::make('New Customers', $customersToday)
                ->description('Registered today')
                ->color('info')
                ->chart([2, 3, 1, 4, 2, 5, 3, 6, 4, 7, 5, 8]),

            Stat::make('Low Stock Products', $lowStockCount)
                ->description('Needs restocking')
                ->color($lowStockCount > 0 ? 'danger' : 'success')
                ->chart([15, 12, 10, 8, 6, 5, 4, 3, 2, 1, 2, 3]),
        ];
    }
}