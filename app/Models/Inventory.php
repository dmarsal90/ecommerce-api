<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Inventory extends Model
{
    protected $fillable = [
        'product_id',
        'variant_id',
        'sku',
        'quantity',
        'reserved',
        'available',
        'low_stock_threshold',
        'location',
        'track_quantity',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'reserved' => 'integer',
        'available' => 'integer',
        'low_stock_threshold' => 'integer',
        'track_quantity' => 'boolean',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function variant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class);
    }

    public function stockMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class);
    }

    public function scopeActive($query)
    {
        return $query->where('track_quantity', true);
    }

    public function scopeLowStock($query)
    {
        return $query->whereRaw('available <= low_stock_threshold');
    }

    public function getLowStockAttribute(): bool
    {
        return $this->track_quantity && $this->available <= $this->low_stock_threshold;
    }

    public function getInStockAttribute(): bool
    {
        if (! $this->track_quantity) {
            return true;
        }

        return $this->available > 0;
    }
}
