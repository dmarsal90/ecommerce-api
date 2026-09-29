<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductVariantAttributeValueResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'variant_id' => $this->variant_id,
            'attribute_id' => $this->attribute_id,
            'value' => $this->value,
            'attribute' => $this->whenLoaded('attribute', fn () => new ProductAttributeResource($this->attribute)),
        ];
    }
}
