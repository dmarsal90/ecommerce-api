<?php

namespace App\Services\Order;

class OrderItemDTO
{
    public function __construct(
        public int $productId,
        public ?int $variantId,
        public int $quantity,
        public float $price,
        public ?float $compareAtPrice,
        public float $total,
        public string $productName,
        public ?string $variantName,
        public string $sku,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            productId: $data['product_id'],
            variantId: $data['variant_id'] ?? null,
            quantity: $data['quantity'],
            price: $data['price'],
            compareAtPrice: $data['compare_at_price'] ?? null,
            total: $data['total'],
            productName: $data['product_name'],
            variantName: $data['variant_name'] ?? null,
            sku: $data['sku'],
        );
    }

    public function toArray(): array
    {
        return [
            'product_id' => $this->productId,
            'variant_id' => $this->variantId,
            'quantity' => $this->quantity,
            'price' => $this->price,
            'compare_at_price' => $this->compareAtPrice,
            'total' => $this->total,
            'product_name' => $this->productName,
            'variant_name' => $this->variantName,
            'sku' => $this->sku,
        ];
    }
}
