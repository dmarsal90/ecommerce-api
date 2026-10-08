<?php

namespace App\Services\Order;

use App\Models\Order;
use App\Models\OrderAddress;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Services\Contracts\PricingCalculatorInterface;
use App\Services\Contracts\StockServiceInterface;
use App\Services\Inventory\StockLockService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class OrderService
{
    public function __construct(
        private PricingCalculatorInterface $pricingCalculator,
        private StockServiceInterface $stockService,
    ) {}

    public function createFromCart(array $validatedData): Order
    {
        $user = Auth::user();
        $cartItems = $validatedData['items'];
        $idempotencyKey = $validatedData['idempotency_key'] ?? null;

        // Check for existing order with same idempotency key
        if ($idempotencyKey) {
            $existingOrder = Order::where('idempotency_key', $idempotencyKey)
                ->where('user_id', $user->id)
                ->first();

            if ($existingOrder) {
                return $existingOrder->load(['items.product.images', 'items.variant.attributeValues.attribute', 'addresses']);
            }
        }

        $items = $this->prepareOrderItems($cartItems);
        $subtotal = $this->pricingCalculator->calculateSubtotal($items);
        $tax = $this->pricingCalculator->calculateTax($subtotal);
        $shipping = $this->pricingCalculator->calculateShipping($subtotal);
        $discount = $this->pricingCalculator->calculateDiscount($items);
        $total = $this->pricingCalculator->calculateTotal($subtotal, $tax, $shipping, $discount);

        $shippingAddress = OrderAddressDTO::fromArray($validatedData['shipping_address'], 'shipping');
        $billingAddressData = $validatedData['billing_address'] ?? $validatedData['shipping_address'];
        $billingAddress = OrderAddressDTO::fromArray($billingAddressData, 'billing');

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

        return DB::transaction(function () use ($orderData, $items, $idempotencyKey) {
            $order = $this->createOrder($orderData, $idempotencyKey);
            $this->createOrderItems($order, $items);
            $this->createOrderAddresses($order, $orderData);
            $this->decrementStock($items);
            session()->forget('cart');

            return $order->load(['items.product.images', 'items.variant.attributeValues.attribute', 'addresses']);
        });
    }

    public function getPricingCalculator(): PricingCalculatorInterface
    {
        return $this->pricingCalculator;
    }

    public function cancelOrder(Order $order): Order
    {
        if (! in_array($order->status, ['pending', 'processing'])) {
            throw new \InvalidArgumentException('Order cannot be cancelled');
        }

        if ($order->status === 'cancelled') {
            throw new \InvalidArgumentException('Order is already cancelled');
        }

        return DB::transaction(function () use ($order) {
            $order->update([
                'status' => 'cancelled',
                'cancelled_at' => now(),
            ]);

            $this->restoreStock($order);

            return $order->load(['items.product.images', 'items.variant.attributeValues.attribute', 'addresses']);
        });
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

    private function createOrder(OrderDataDTO $orderData, ?string $idempotencyKey = null): Order
    {
        return Order::create(array_merge($orderData->toArray(), [
            'order_number' => 'ORD-' . strtoupper(Str::random(8)),
            'idempotency_key' => $idempotencyKey,
            'status' => 'pending',
            'payment_status' => 'pending',
            'fulfillment_status' => 'unfulfilled',
        ]));
    }

    private function createOrderItems(Order $order, array $items): void
    {
        foreach ($items as $item) {
            OrderItem::create(array_merge($item->toArray(), ['order_id' => $order->id]));
        }
    }

    private function createOrderAddresses(Order $order, OrderDataDTO $orderData): void
    {
        OrderAddress::create(array_merge($orderData->shippingAddress->toArray(), ['order_id' => $order->id]));
        OrderAddress::create(array_merge($orderData->billingAddress->toArray(), ['order_id' => $order->id]));
    }

    private function decrementStock(array $items): void
    {
        foreach ($items as $item) {
            $model = $item->variantId
                ? ProductVariant::find($item->variantId)
                : Product::find($item->productId);

            if ($model && $model->track_quantity) {
                $this->stockService->decrementStock($model, $item->quantity);
            }
        }
    }

    private function restoreStock(Order $order): void
    {
        foreach ($order->items as $item) {
            $model = $item->variant_id
                ? ProductVariant::find($item->variant_id)
                : Product::find($item->product_id);

            if ($model && $model->track_quantity) {
                $this->stockService->incrementStock($model, $item->quantity);
            }
        }
    }

    public function decrementStockForOrder(Order $order): void
    {
        foreach ($order->items as $item) {
            $model = $item->variant_id
                ? ProductVariant::find($item->variant_id)
                : Product::find($item->product_id);

            if ($model && $model->track_quantity) {
                $this->stockService->decrementStock($model, $item->quantity);
            }
        }
    }
}