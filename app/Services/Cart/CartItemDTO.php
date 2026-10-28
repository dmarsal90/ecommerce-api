<?php

namespace App\Services\Cart;

class CartItemDTO
{
    public function __construct(
        public string $key,
        public int $productId,
        public ?int $variantId,
        public int $quantity,
        public float $price,
        public float $total,
        public array $product,
        public ?array $variant,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            key: $data['key'],
            productId: $data['product_id'],
            variantId: $data['variant_id'] ?? null,
            quantity: $data['quantity'],
            price: $data['price'],
            total: $data['total'],
            product: $data['product'],
            variant: $data['variant'] ?? null,
        );
    }

    public function toArray(): array
    {
        return [
            'key' => $this->key,
            'product_id' => $this->productId,
            'variant_id' => $this->variantId,
            'quantity' => $this->quantity,
            'price' => $this->price,
            'total' => $this->total,
            'product' => $this->product,
            'variant' => $this->variant,
        ];
    }
}
