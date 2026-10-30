<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class ProductImageResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            // The table stores a path on the public disk (or an absolute URL); clients need a URL they can load.
            'url' => preg_match('#^https?://#', $this->path) ? $this->path : Storage::disk('public')->url($this->path),
            'alt' => $this->alt_text,
            'position' => $this->sort_order,
        ];
    }
}
