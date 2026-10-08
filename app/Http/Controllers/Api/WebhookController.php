<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\Order\OrderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WebhookController extends Controller
{
    public function __construct(
        private OrderService $orderService,
    ) {}

    public function stripe(Request $request): JsonResponse
    {
        $payload = $request->getContent();
        $signature = $request->header('Stripe-Signature');

        try {
            $event = app(\App\Services\Contracts\PaymentGatewayInterface::class)->handleWebhook($payload, $signature);

            $this->handleStripeEvent($event);

            return response()->json(['received' => true]);
        } catch (\InvalidArgumentException $e) {
            Log::error('Stripe webhook error: ' . $e->getMessage());

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

        $order = Order::where('payment_reference', $paymentIntentId)->first();

        if (! $order) {
            Log::warning("Order not found for payment intent: {$paymentIntentId}");
            return;
        }

        if ($order->payment_status !== 'paid') {
            $order->update([
                'payment_status' => 'paid',
                'status' => 'paid',
                'paid_at' => now(),
            ]);

            // Decrement stock for the order
            // Note: Stock was already decremented at order creation
            // This is just a safety check
            $this->orderService->decrementStockForOrder($order);
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

        if (! $order) {
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

        if (! $order) {
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