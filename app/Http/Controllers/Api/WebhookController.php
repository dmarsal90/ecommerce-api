<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\StripeEvent;
use App\Services\Contracts\PaymentGatewayInterface;
use App\Services\Order\OrderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class WebhookController extends Controller
{
    public function __construct(
        private OrderService $orderService,
    ) {}

    public function stripe(Request $request): JsonResponse
    {
        $payload = $request->getContent();
        $signature = (string) $request->header('Stripe-Signature');

        try {
            $event = app(PaymentGatewayInterface::class)->handleWebhook($payload, $signature);

            // Claim and handle atomically: a failed handler rolls the claim back so Stripe's retry is processed.
            DB::transaction(function () use ($event) {
                if (StripeEvent::insertOrIgnore(['id' => $event['id'], 'type' => $event['type']]) === 0) {
                    return; // replay or retry of an event we already applied
                }

                $this->handleStripeEvent($event);
            });

            return response()->json(['received' => true]);
        } catch (\InvalidArgumentException $e) {
            Log::error('Stripe webhook error: '.$e->getMessage());

            return response()->json(['error' => 'Invalid webhook'], 400);
        }
    }

    private function handleStripeEvent(array $event): void
    {
        $type = $event['type'];
        $data = $event['data'];

        match ($type) {
            'payment_intent.succeeded' => $this->handlePaymentSucceeded($data),
            'payment_intent.payment_failed' => $this->handlePaymentFailed($data),
            'charge.refunded' => $this->handleRefund($data),
            default => null,
        };
    }

    private function handlePaymentSucceeded(array $data): void
    {
        $paymentIntentId = $data['id'] ?? null;
        $metadata = $data['metadata'] ?? [];

        if (! $paymentIntentId || ! isset($metadata['order_id'])) {
            return;
        }

        $order = Order::where('payment_reference', $paymentIntentId)->lockForUpdate()->first();

        if (! $order) {
            Log::warning("Order not found for payment intent: {$paymentIntentId}");

            return;
        }

        // Never trust the event alone: it must describe exactly this order and exactly what it costs.
        $received = $data['amount_received'] ?? $data['amount'] ?? null;
        if ((string) $metadata['order_id'] !== (string) $order->id
            || $received !== (int) round($order->total * 100)
            || strtolower((string) ($data['currency'] ?? '')) !== strtolower($order->currency)) {
            Log::critical('Stripe payment does not match its order', ['order_id' => $order->id, 'payment_intent' => $paymentIntentId]);

            return;
        }

        // The order was cancelled (or its payment failed) before the money arrived: give it back.
        if (in_array($order->status, ['cancelled', 'payment_failed'], true)) {
            app(PaymentGatewayInterface::class)->refund($paymentIntentId);
            $order->update(['payment_status' => 'refunded']);
            Log::warning('Refunded a payment received for an inactive order', ['order_id' => $order->id]);

            return;
        }

        // Stock was already decremented at order creation
        if ($order->payment_status !== 'paid') {
            $order->update([
                'payment_status' => 'paid',
                'status' => 'paid',
                'paid_at' => now(),
            ]);
        }
    }

    private function handlePaymentFailed(array $data): void
    {
        $paymentIntentId = $data['id'] ?? null;
        $metadata = $data['metadata'] ?? [];

        if (! $paymentIntentId || ! isset($metadata['order_id'])) {
            return;
        }

        $order = Order::where('payment_reference', $paymentIntentId)->first();

        if (! $order || $order->payment_status !== 'pending') {
            return;
        }

        $order->update([
            'payment_status' => 'failed',
            'status' => 'payment_failed',
        ]);

        // Restore stock since payment failed
        $this->orderService->restoreStock($order);
    }

    private function handleRefund(array $data): void
    {
        $paymentIntentId = $data['payment_intent'] ?? null;

        if (! $paymentIntentId) {
            return;
        }

        $order = Order::where('payment_reference', $paymentIntentId)->first();

        if (! $order || $order->payment_status === 'refunded') {
            return;
        }

        $order->update([
            'payment_status' => 'refunded',
            'status' => 'refunded',
        ]);

        // Restore stock for refunded order
        $this->orderService->restoreStock($order);
    }
}
