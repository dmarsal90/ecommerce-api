<?php

namespace App\Http\Resources\Api;

use App\Support\MediaUrl;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductImageResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'url' => MediaUrl::public($this->path),
            'alt' => $this->alt_text,
            'position' => $this->sort_order,
        ];
    }
}
