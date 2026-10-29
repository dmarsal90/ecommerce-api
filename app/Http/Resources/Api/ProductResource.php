<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'sku' => $this->sku,
            'description' => $this->description,
            'short_description' => $this->short_description,
            'price' => $this->price,
            'compare_at_price' => $this->compare_at_price,
            'is_active' => $this->is_active,
            'is_featured' => $this->is_featured,
            'gender' => $this->gender,
            'season' => $this->season,
            'quantity' => $this->quantity,
            'track_quantity' => $this->track_quantity,
            'category' => $this->whenLoaded('category', fn () => new CategoryResource($this->category)),
            'brand' => $this->whenLoaded('brand', fn () => new BrandResource($this->brand)),
            'images' => $this->whenLoaded('images', fn () => ProductImageResource::collection($this->images)),
            'variants' => $this->whenLoaded('variants', fn () => ProductVariantResource::collection($this->variants)),
            'collections' => $this->whenLoaded('collections', fn () => CollectionResource::collection($this->collections)),
            'tags' => $this->whenLoaded('tags', fn () => TagResource::collection($this->tags)),
            'reviews' => $this->whenLoaded('reviews', fn () => ReviewResource::collection($this->reviews)),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
