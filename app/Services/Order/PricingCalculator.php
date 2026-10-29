<?php

namespace App\Services\Order;

use App\Services\Contracts\PricingCalculatorInterface;

class PricingCalculator implements PricingCalculatorInterface
{
    public function calculateSubtotal(array $items): float
    {
        $subtotal = 0;
        foreach ($items as $item) {
            $price = $item instanceof OrderItemDTO ? $item->price : ($item['price'] ?? 0);
            $quantity = $item instanceof OrderItemDTO ? $item->quantity : ($item['quantity'] ?? 0);
            $subtotal += $price * $quantity;
        }

        return round($subtotal, 2);
    }

    public function calculateTax(float $subtotal): float
    {
        $taxRate = config('shop.tax_rate', 0.08);

        return round($subtotal * $taxRate, 2);
    }

    public function calculateShipping(float $subtotal): float
    {
        $freeShippingThreshold = config('shop.free_shipping_threshold', 50);
        $shippingCost = config('shop.shipping_cost', 9.99);

        return $subtotal > $freeShippingThreshold ? 0 : $shippingCost;
    }

    public function calculateDiscount(array $items): float
    {
        // Extensible for future discount logic (coupons, loyalty, etc.)
        return 0;
    }

    public function calculateTotal(float $subtotal, float $tax, float $shipping, float $discount): float
    {
        return round($subtotal + $tax + $shipping - $discount, 2);
    }
}
