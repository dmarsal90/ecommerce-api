<?php

namespace Tests\Feature\Filament;

use App\Filament\Widgets\StatsOverview;
use App\Models\Order;
use Illuminate\Foundation\Testing\RefreshDatabase;
use ReflectionMethod;
use Tests\TestCase;

class StatsOverviewTest extends TestCase
{
    use RefreshDatabase;

    private function stats(): array
    {
        $method = new ReflectionMethod(StatsOverview::class, 'getStats');

        return $method->invoke(new StatsOverview);
    }

    public function test_sales_chart_is_a_real_seven_day_series_with_trend(): void
    {
        Order::factory()->create(['payment_status' => 'paid', 'paid_at' => now()->subDay(), 'total' => 100]);
        Order::factory()->create(['payment_status' => 'paid', 'paid_at' => now(), 'total' => 150]);

        $sales = $this->stats()[0];

        $this->assertSame([0.0, 0.0, 0.0, 0.0, 0.0, 100.0, 150.0], $sales->getChart());
        $this->assertSame('+50% vs yesterday', $sales->getDescription());
    }

    public function test_trend_is_neutral_without_activity_yesterday(): void
    {
        $orders = $this->stats()[1];

        $this->assertSame('No activity yesterday', $orders->getDescription());
        $this->assertNull($orders->getDescriptionIcon());
    }
}
