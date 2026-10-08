<?php

namespace App\Services\Order;

class OrderDataDTO
{
    public function __construct(
        public int $userId,
        public string $email,
        public ?string $phone,
        public string $paymentMethod,
        public ?string $notes,
        public array $items,
        public OrderAddressDTO $shippingAddress,
        public OrderAddressDTO $billingAddress,
        public float $subtotal,
        public float $tax,
        public float $shipping,
        public float $discount,
        public float $total,
        public string $currency,
    ) {}

    public function toArray(): array
    {
        return [
            'user_id' => $this->userId,
            'email' => $this->email,
            'phone' => $this->phone,
            'payment_method' => $this->paymentMethod,
            'notes' => $this->notes,
            'subtotal' => $this->subtotal,
            'tax' => $this->tax,
            'shipping' => $this->shipping,
            'discount' => $this->discount,
            'total' => $this->total,
            'currency' => $this->currency,
        ];
    }
}