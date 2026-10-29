<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Number;

class StatsOverview extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $since = now()->subDays(6)->startOfDay();

        $sales = $this->dailySeries(Order::where('payment_status', 'paid'), 'paid_at', $since, 'sum(total)');
        $orders = $this->dailySeries(Order::query(), 'created_at', $since);
        $customers = $this->dailySeries(User::query(), 'created_at', $since);

        $lowStockCount = Product::where('track_quantity', true)
            ->whereRaw('quantity <= low_stock_threshold')
            ->count();

        $currency = config('shop.currency', 'USD');

        return [
            Stat::make('Sales today', Number::currency(end($sales), $currency))
                ->description($this->trend($sales, 'vs yesterday'))
                ->descriptionIcon($this->trendIcon($sales))
                ->color('success')
                ->chart($sales),

            Stat::make('Orders today', end($orders))
                ->description($this->trend($orders, 'vs yesterday'))
                ->descriptionIcon($this->trendIcon($orders))
                ->color('primary')
                ->chart($orders),

            Stat::make('New customers today', end($customers))
                ->description($this->trend($customers, 'vs yesterday'))
                ->descriptionIcon($this->trendIcon($customers))
                ->color('info')
                ->chart($customers),

            Stat::make('Low stock products', $lowStockCount)
                ->description($lowStockCount > 0 ? 'Needs restocking' : 'All stocked')
                ->descriptionIcon($lowStockCount > 0 ? 'heroicon-m-exclamation-triangle' : 'heroicon-m-check-circle')
                ->color($lowStockCount > 0 ? 'danger' : 'success'),
        ];
    }

    /** Values for the last 7 days, oldest first, filling missing days with 0. */
    private function dailySeries(Builder $query, string $column, Carbon $since, string $aggregate = 'count(*)'): array
    {
        $rows = $query->where($column, '>=', $since)
            ->selectRaw("date({$column}) as day, {$aggregate} as value")
            ->groupBy('day')
            ->pluck('value', 'day');

        return collect(range(0, 6))
            ->map(fn (int $i) => (float) ($rows[$since->copy()->addDays($i)->toDateString()] ?? 0))
            ->all();
    }

    private function trend(array $series, string $suffix): string
    {
        [$yesterday, $today] = array_slice($series, -2);
        $change = $yesterday > 0 ? round(($today - $yesterday) / $yesterday * 100) : null;

        return $change === null ? 'No activity yesterday' : ($change >= 0 ? '+' : '').$change.'% '.$suffix;
    }

    private function trendIcon(array $series): ?string
    {
        [$yesterday, $today] = array_slice($series, -2);

        if ($yesterday == 0) {
            return null;
        }

        return $today >= $yesterday ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down';
    }
}
