<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class ProductVariant extends Model
{
    protected $fillable = [
        'product_id',
        'sku',
        'price',
        'compare_at_price',
        'cost_price',
        'quantity',
        'low_stock_threshold',
        'weight',
        'barcode',
        'image',
        'is_active',
        'track_quantity',
        'meta_data',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'compare_at_price' => 'decimal:2',
        'cost_price' => 'decimal:2',
        'quantity' => 'integer',
        'low_stock_threshold' => 'integer',
        'weight' => 'decimal:2',
        'is_active' => 'boolean',
        'track_quantity' => 'boolean',
        'meta_data' => 'array',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function attributeValues(): BelongsToMany
    {
        return $this->belongsToMany(ProductAttributeValue::class, 'product_variant_attribute_values', 'variant_id', 'attribute_value_id')
            ->using(ProductVariantAttributeValue::class)
            ->withPivot('id')
            ->withTimestamps();
    }

    public function variantAttributeValues(): HasMany
    {
        return $this->hasMany(ProductVariantAttributeValue::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function getLowStockAttribute(): bool
    {
        return $this->track_quantity && $this->quantity <= $this->low_stock_threshold;
    }

    public function getInStockAttribute(): bool
    {
        if (!$this->track_quantity) {
            return true;
        }
        return $this->quantity > 0;
    }

    public function inventory(): HasOne
    {
        return $this->hasOne(Inventory::class, 'variant_id');
    }

    public function getFormattedAttributes(): array
    {
        return $this->attributeValues()
            ->with('attribute')
            ->get()
            ->groupBy('attribute.name')
            ->map(fn ($values) => $values->pluck('value')->implode(', '))
            ->toArray();
    }
}