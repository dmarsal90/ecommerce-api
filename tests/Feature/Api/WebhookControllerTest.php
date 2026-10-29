<?php

namespace Tests\Feature\Api;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Services\Contracts\PaymentGatewayInterface;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery\MockInterface;
use Tests\TestCase;

class WebhookControllerTest extends TestCase
{
    use RefreshDatabase;

    private function postEvent(string $type, array $object)
    {
        $this->mock(PaymentGatewayInterface::class, fn (MockInterface $m) => $m
            ->shouldReceive('handleWebhook')->andReturn(['id' => 'evt_'.uniqid(), 'type' => $type, 'data' => $object]));

        return $this->postJson('/api/webhooks/stripe', [], ['Stripe-Signature' => 'sig']);
    }

    private function pendingOrder(): array
    {
        $product = Product::factory()->create(['quantity' => 10, 'track_quantity' => true]);
        $order = Order::factory()->create(['payment_status' => 'pending', 'status' => 'pending', 'payment_reference' => 'pi_1', 'total' => 25.5, 'currency' => 'USD']);
        OrderItem::factory()->create(['order_id' => $order->id, 'product_id' => $product->id, 'variant_id' => null, 'quantity' => 3]);

        return [$order, $product];
    }

    public function test_payment_failed_restores_stock_once(): void
    {
        [$order, $product] = $this->pendingOrder();
        $event = ['id' => 'pi_1', 'metadata' => ['order_id' => $order->id]];

        $this->postEvent('payment_intent.payment_failed', $event)->assertOk();
        $this->postEvent('payment_intent.payment_failed', $event)->assertOk();

        $this->assertSame('failed', $order->fresh()->payment_status);
        $this->assertSame(13, $product->fresh()->quantity);
    }

    public function test_payment_succeeded_marks_paid_without_touching_stock(): void
    {
        [$order, $product] = $this->pendingOrder();

        $this->postEvent('payment_intent.succeeded', ['id' => 'pi_1', 'amount' => 2550, 'currency' => 'usd', 'metadata' => ['order_id' => $order->id]])->assertOk();

        $this->assertSame('paid', $order->fresh()->payment_status);
        $this->assertSame(10, $product->fresh()->quantity);
    }

    public function test_payment_with_wrong_amount_is_not_marked_paid(): void
    {
        [$order] = $this->pendingOrder();

        $this->postEvent('payment_intent.succeeded', ['id' => 'pi_1', 'amount' => 100, 'currency' => 'usd', 'metadata' => ['order_id' => $order->id]])->assertOk();

        $this->assertSame('pending', $order->fresh()->payment_status);
    }

    public function test_replayed_event_is_applied_once(): void
    {
        [$order] = $this->pendingOrder();
        $this->mock(PaymentGatewayInterface::class, fn (MockInterface $m) => $m
            ->shouldReceive('handleWebhook')->andReturn(['id' => 'evt_same', 'type' => 'payment_intent.succeeded',
                'data' => ['id' => 'pi_1', 'amount' => 2550, 'currency' => 'usd', 'metadata' => ['order_id' => $order->id]]]));

        $this->postJson('/api/webhooks/stripe', [], ['Stripe-Signature' => 'sig'])->assertOk();
        Order::whereKey($order->id)->update(['payment_status' => 'pending']);
        $this->postJson('/api/webhooks/stripe', [], ['Stripe-Signature' => 'sig'])->assertOk();

        $this->assertSame('pending', $order->fresh()->payment_status);
    }
}
