<?php

namespace App\Services\Order;

use App\Models\Order;
use App\Services\Contracts\PaymentGatewayInterface;
use App\Services\Contracts\StockServiceInterface;
use App\Services\Inventory\StockLockService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CheckoutService
{
    public function __construct(
        private PaymentGatewayInterface $paymentGateway,
        private StockServiceInterface $stockService,
    ) {}

    public function createCheckoutSession(array $validatedData): array
    {
        $user = Auth::user();
        $cartItems = $validatedData['items'];

        // Check for existing order with same idempotency key
        $idempotencyKey = $validatedData['idempotency_key'] ?? null;
        if ($idempotencyKey) {
            $existingOrder = Order::where('idempotency_key', $idempotencyKey)
                ->where('user_id', $user->id)
                ->first();

            if ($existingOrder) {
                // Return existing payment intent if order is still pending payment
                if ($existingOrder->payment_status === 'pending' && $existingOrder->payment_reference) {
                    return [
                        'order' => $existingOrder->load(['items.product.images', 'items.variant.attributeValues.attribute', 'addresses']),
                        'payment_intent' => [
                            'id' => $existingOrder->payment_reference,
                            'client_secret' => $existingOrder->meta_data['client_secret'] ?? null,
                        ],
                    ];
                }

                return [
                    'order' => $existingOrder->load(['items.product.images', 'items.variant.attributeValues.attribute', 'addresses']),
                    'payment_intent' => null,
                ];
            }
        }

        // Prepare order items and validate stock
        $items = $this->prepareOrderItems($cartItems);

        // Calculate totals
        $orderService = app(\App\Services\Order\OrderService::class);
        $pricingCalculator = $orderService->getPricingCalculator();

        $subtotal = $pricingCalculator->calculateSubtotal($items);
        $tax = $pricingCalculator->calculateTax($subtotal);
        $shipping = $pricingCalculator->calculateShipping($subtotal);
        $discount = $pricingCalculator->calculateDiscount($items);
        $total = $pricingCalculator->calculateTotal($subtotal, $tax, $shipping, $discount);

        $shippingAddress = \App\Services\Order\OrderAddressDTO::fromArray($validatedData['shipping_address'], 'shipping');
        $billingAddressData = $validatedData['billing_address'] ?? $validatedData['shipping_address'];
        $billingAddress = \App\Services\Order\OrderAddressDTO::fromArray($billingAddressData, 'billing');

        $orderData = new \App\Services\Order\OrderDataDTO(
            userId: $user->id,
            email: $user->email,
            phone: $validatedData['shipping_address']['phone'] ?? null,
            paymentMethod: $validatedData['payment_method'],
            notes: $validatedData['notes'] ?? null,
            items: array_map(fn ($item) => $item->toArray(), $items),
            shippingAddress: $shippingAddress,
            billingAddress: $billingAddress,
            subtotal: $subtotal,
            tax: $tax,
            shipping: $shipping,
            discount: $discount,
            total: $total,
            currency: config('shop.currency', 'USD'),
        );

        return DB::transaction(function () use ($orderData, $items, $idempotencyKey, $validatedData) {
            // Create order with pending payment status
            $order = Order::create(array_merge($orderData->toArray(), [
                'order_number' => 'ORD-' . strtoupper(Str::random(8)),
                'idempotency_key' => $idempotencyKey,
                'status' => 'pending',
                'payment_status' => 'pending',
                'fulfillment_status' => 'unfulfilled',
            ]));

            // Create order items
            foreach ($items as $item) {
                \App\Models\OrderItem::create(array_merge($item->toArray(), ['order_id' => $order->id]));
            }

            // Create addresses
            \App\Models\OrderAddress::create(array_merge($orderData->shippingAddress->toArray(), ['order_id' => $order->id]));
            \App\Models\OrderAddress::create(array_merge($orderData->billingAddress->toArray(), ['order_id' => $order->id]));

            // Create payment intent for card payments
            $paymentIntent = null;
            if ($validatedData['payment_method'] === 'credit_card' || $validatedData['payment_method'] === 'stripe') {
                $paymentIntent = $this->paymentGateway->createPaymentIntent(
                    $total,
                    config('shop.currency', 'USD'),
                    [
                        'order_id' => $order->id,
                        'order_number' => $order->order_number,
                        'user_id' => $user->id,
                    ]
                );

                $order->update([
                    'payment_reference' => $paymentIntent['id'],
                    'meta_data' => array_merge($order->meta_data ?? [], [
                        'client_secret' => $paymentIntent['client_secret'],
                    ]),
                ]);
            }

            // Clear cart
            session()->forget('cart');

            return [
                'order' => $order->load(['items.product.images', 'items.variant.attributeValues.attribute', 'addresses']),
                'payment_intent' => $paymentIntent,
            ];
        });
    }

    private function prepareOrderItems(array $cartItems): array
    {
        $items = [];

        foreach ($cartItems as $itemData) {
            $product = \App\Models\Product::findOrFail($itemData['product_id']);
            $variant = isset($itemData['variant_id']) ? \App\Models\ProductVariant::findOrFail($itemData['variant_id']) : null;

            if (! $product->is_active || ($variant && ! $variant->is_active)) {
                throw new \InvalidArgumentException('Product or variant is not available');
            }

            $quantity = $itemData['quantity'];

            if (! $this->stockService->checkStock($variant ?? $product, $quantity)) {
                $available = $this->stockService->getAvailableStock($variant ?? $product);
                throw new \InvalidArgumentException("Not enough stock available. Available: {$available}");
            }

            if ($variant) {
                $price = $variant->price;
                $sku = $variant->sku;
                $variantName = $variant->sku;
                $compareAtPrice = $variant->compare_at_price;
            } else {
                $price = $product->price;
                $sku = $product->sku;
                $variantName = null;
                $compareAtPrice = $product->compare_at_price;
            }

            $total = $price * $quantity;

            $items[] = new \App\Services\Order\OrderItemDTO(
                productId: $product->id,
                variantId: $variant?->id,
                quantity: $quantity,
                price: $price,
                compareAtPrice: $compareAtPrice,
                total: $total,
                productName: $product->name,
                variantName: $variantName,
                sku: $sku,
            );
        }

        return $items;
    }
}