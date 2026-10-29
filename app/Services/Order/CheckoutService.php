<?php

namespace App\Services\Order;

use App\Http\Requests\Api\StoreOrderRequest;
use App\Models\Order;
use App\Models\OrderAddress;
use App\Models\OrderItem;
use App\Models\PaymentMethod;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use App\Services\Contracts\PaymentGatewayInterface;
use App\Services\Contracts\StockServiceInterface;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CheckoutService
{
    /** Stripe's minimum charge is about 0.50 in USD/EUR. */
    private const MIN_CARD_TOTAL = 0.50;

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
        $orderService = app(OrderService::class);
        $pricingCalculator = $orderService->getPricingCalculator();

        $subtotal = $pricingCalculator->calculateSubtotal($items);
        $tax = $pricingCalculator->calculateTax($subtotal);
        $coupon = $orderService->resolveCoupon($validatedData['coupon_code'] ?? null, $items, $subtotal);
        $shipping = $coupon?->type === 'free_shipping' ? 0.0 : $pricingCalculator->calculateShipping($subtotal);
        $discount = $coupon ? $coupon->calculateDiscount($subtotal) : $pricingCalculator->calculateDiscount($items);
        $total = $pricingCalculator->calculateTotal($subtotal, $tax, $shipping, $discount);

        $shippingAddress = OrderAddressDTO::fromArray($validatedData['shipping_address'], 'shipping');
        $billingAddressData = $validatedData['billing_address'] ?? $validatedData['shipping_address'];
        $billingAddress = OrderAddressDTO::fromArray($billingAddressData, 'billing');

        // Card payments are validated before anything is written or reserved.
        $isCard = in_array($validatedData['payment_method'], StoreOrderRequest::CARD_METHODS, true);
        $savedCard = $isCard ? $this->resolveSavedCard($user, $validatedData['payment_method_id'] ?? null, $total) : null;

        $orderData = new OrderDataDTO(
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

        return DB::transaction(function () use ($orderData, $items, $idempotencyKey, $orderService, $coupon, $savedCard, $total, $user) {
            // Create order with pending payment status
            $order = Order::create(array_merge($orderData->toArray(), [
                'order_number' => 'ORD-'.strtoupper(Str::random(8)),
                'idempotency_key' => $idempotencyKey,
                'status' => 'pending',
                'payment_status' => 'pending',
                'fulfillment_status' => 'unfulfilled',
            ]));

            // Create order items
            foreach ($items as $item) {
                OrderItem::create(array_merge($item->toArray(), ['order_id' => $order->id]));
            }

            $orderService->decrementStock($items);

            if ($coupon) {
                $orderService->recordCoupon($order, $coupon);
            }

            // Create addresses
            OrderAddress::create(array_merge($orderData->shippingAddress->toArray(), ['order_id' => $order->id]));
            OrderAddress::create(array_merge($orderData->billingAddress->toArray(), ['order_id' => $order->id]));

            // Create the card PaymentIntent with the customer's saved card; the browser confirms it (3-D Secure).
            $paymentIntent = null;
            if ($savedCard) {
                $this->paymentGateway->updatePaymentMethodBilling($savedCard->stripe_payment_method_id, $this->stripeAddress($orderData->billingAddress));

                $paymentIntent = $this->paymentGateway->createPaymentIntent(
                    $total,
                    config('shop.currency', 'USD'),
                    [
                        'order_id' => $order->id,
                        'order_number' => $order->order_number,
                        'user_id' => $user->id,
                    ],
                    [
                        'customer' => $user->stripe_customer_id,
                        'payment_method' => $savedCard->stripe_payment_method_id,
                        'shipping' => $this->stripeAddress($orderData->shippingAddress),
                        'idempotency_key' => "order-{$order->order_number}",
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

    private function resolveSavedCard(User $user, ?int $paymentMethodId, float $total): PaymentMethod
    {
        $card = $paymentMethodId ? PaymentMethod::where('user_id', $user->id)->find($paymentMethodId) : null;

        if (! $card || ! $user->stripe_customer_id) {
            throw new \InvalidArgumentException('Invalid payment method');
        }

        if ($card->isExpired()) {
            throw new \InvalidArgumentException('This card has expired');
        }

        if ($total < self::MIN_CARD_TOTAL) {
            throw new \InvalidArgumentException('The order total is too low to pay by card');
        }

        return $card;
    }

    /** @return array{name: string, address: array<string, ?string>} */
    private function stripeAddress(OrderAddressDTO $address): array
    {
        return [
            'name' => trim($address->firstName.' '.$address->lastName),
            'address' => [
                'line1' => $address->addressLine1,
                'line2' => $address->addressLine2,
                'city' => $address->city,
                'state' => $address->state,
                'postal_code' => $address->postalCode,
                'country' => $address->country,
            ],
        ];
    }

    private function prepareOrderItems(array $cartItems): array
    {
        $items = [];

        foreach ($cartItems as $itemData) {
            $product = Product::findOrFail($itemData['product_id']);
            $variant = isset($itemData['variant_id']) ? ProductVariant::findOrFail($itemData['variant_id']) : null;

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

            $items[] = new OrderItemDTO(
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
