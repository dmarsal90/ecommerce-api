<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductVariantResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'product_id' => $this->product_id,
            'sku' => $this->sku,
            'price' => $this->price,
            'compare_at_price' => $this->compare_at_price,
            'quantity' => $this->quantity,
            'track_quantity' => $this->track_quantity,
            'is_active' => $this->is_active,
            'position' => $this->position,
            'attribute_values' => $this->whenLoaded('attributeValues', fn () => ProductVariantAttributeValueResource::collection($this->attributeValues)),
            'inventory' => $this->whenLoaded('inventory', fn () => new InventoryResource($this->inventory)),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
