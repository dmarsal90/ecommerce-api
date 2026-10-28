<?php

namespace App\Services\Contracts;

interface PricingCalculatorInterface
{
    public function calculateSubtotal(array $items): float;

    public function calculateTax(float $subtotal): float;

    public function calculateShipping(float $subtotal): float;

    public function calculateDiscount(array $items): float;

    public function calculateTotal(float $subtotal, float $tax, float $shipping, float $discount): float;
}
