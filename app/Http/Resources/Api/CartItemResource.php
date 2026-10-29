<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CartItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'key' => $this['key'],
            'product_id' => $this['product_id'],
            'variant_id' => $this['variant_id'] ?? null,
            'quantity' => $this['quantity'],
            'price' => $this['price'],
            'total' => $this['total'],
            'product' => [
                'id' => $this['product']['id'],
                'name' => $this['product']['name'],
                'slug' => $this['product']['slug'],
                'sku' => $this['product']['sku'],
                'price' => $this['product']['price'],
                'compare_at_price' => $this['product']['compare_at_price'],
                'images' => $this['product']['images'],
            ],
            'variant' => $this['variant'] ? [
                'id' => $this['variant']['id'],
                'sku' => $this['variant']['sku'],
                'price' => $this['variant']['price'],
                'attributes' => $this['variant']['attributes'],
            ] : null,
        ];
    }
}
