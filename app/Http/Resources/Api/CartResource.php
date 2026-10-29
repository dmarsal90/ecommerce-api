<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CartResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $items = $this->resource['items'] ?? [];
        $subtotal = $this->resource['subtotal'] ?? 0;
        $totalQuantity = $this->resource['total_quantity'] ?? 0;

        return [
            'items' => CartItemResource::collection($items),
            'subtotal' => $subtotal,
            'total_quantity' => $totalQuantity,
            'item_count' => count($items),
        ];
    }
}
