<?php

namespace Tests\Feature\Api;

use App\Models\Order;
use App\Services\Contracts\PaymentGatewayInterface;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\FakePaymentGateway;
use Tests\TestCase;

class ExpireUnpaidOrdersTest extends TestCase
{
    use RefreshDatabase;

    public function test_only_old_unpaid_card_orders_are_cancelled_and_their_intent_closed(): void
    {
        $stripe = new FakePaymentGateway;
        $this->app->instance(PaymentGatewayInterface::class, $stripe);

        $base = ['status' => 'pending', 'payment_status' => 'pending'];
        $old = Order::factory()->create($base + ['payment_reference' => 'pi_old', 'created_at' => now()->subHours(2)]);
        $fresh = Order::factory()->create($base + ['payment_reference' => 'pi_new']);
        $transfer = Order::factory()->create($base + ['payment_reference' => null, 'created_at' => now()->subHours(2)]);
        $paid = Order::factory()->create(['status' => 'pending', 'payment_status' => 'paid', 'payment_reference' => 'pi_paid', 'created_at' => now()->subHours(2)]);

        $this->artisan('orders:expire-unpaid')->assertSuccessful();

        $this->assertSame('cancelled', $old->fresh()->status);
        $this->assertSame(['pi_old'], $stripe->cancelled);
        foreach ([$fresh, $transfer, $paid] as $order) {
            $this->assertSame('pending', $order->fresh()->status);
        }
    }
}
