<?php

namespace App\Services\Cart;

class CartDTO
{
    public function __construct(
        public array $items,
        public float $subtotal,
        public int $totalQuantity,
    ) {}

    public function toArray(): array
    {
        return [
            'items' => array_map(fn ($item) => $item->toArray(), $this->items),
            'subtotal' => $this->subtotal,
            'total_quantity' => $this->totalQuantity,
        ];
    }
}