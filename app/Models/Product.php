<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Product extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'short_description',
        'category_id',
        'brand_id',
        'sku',
        'price',
        'compare_at_price',
        'cost_price',
        'quantity',
        'low_stock_threshold',
        'material',
        'gender',
        'season',
        'is_active',
        'is_featured',
        'track_quantity',
        'meta_data',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'compare_at_price' => 'decimal:2',
        'cost_price' => 'decimal:2',
        'quantity' => 'integer',
        'low_stock_threshold' => 'integer',
        'is_active' => 'boolean',
        'is_featured' => 'boolean',
        'track_quantity' => 'boolean',
        'meta_data' => 'array',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function variants(): HasMany
    {
        return $this->hasMany(ProductVariant::class)->where('is_active', true);
    }

    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class, 'product_id')->orderBy('sort_order');
    }

    public function primaryImage(): HasMany
    {
        return $this->images()->where('is_primary', true);
    }

    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }

    public function inventory(): HasOne
    {
        return $this->hasOne(Inventory::class, 'product_id')->whereNull('variant_id');
    }

    public function collections(): BelongsToMany
    {
        return $this->belongsToMany(Collection::class, 'product_collection')
            ->withPivot('sort_order')
            ->withTimestamps()
            ->orderBy('sort_order');
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class, 'product_tag')->withTimestamps();
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class)->where('status', 'approved')->orderBy('created_at', 'desc');
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($product) {
            if (empty($product->slug)) {
                $product->slug = self::generateUniqueSlug($product->name);
            }
            if (empty($product->sku)) {
                $product->sku = self::generateUniqueSku();
            }
        });

        static::created(function ($product) {
            if ($product->track_quantity) {
                $product->inventory()->create([
                    'sku' => $product->sku,
                    'quantity' => $product->quantity,
                    'reserved' => 0,
                    'available' => $product->quantity,
                    'low_stock_threshold' => $product->low_stock_threshold,
                    'location' => 'Main Warehouse',
                    'track_quantity' => true,
                ]);
            }
        });

        static::updating(function ($product) {
            if ($product->isDirty('name') && empty($product->slug)) {
                $product->slug = self::generateUniqueSlug($product->name);
            }
        });

        static::updated(function ($product) {
            if ($product->track_quantity) {
                $inventory = $product->inventory;
                if ($inventory) {
                    $inventory->update([
                        'quantity' => $product->quantity,
                        'available' => $product->quantity - $inventory->reserved,
                        'low_stock_threshold' => $product->low_stock_threshold,
                        'track_quantity' => true,
                    ]);
                } else {
                    $product->inventory()->create([
                        'sku' => $product->sku,
                        'quantity' => $product->quantity,
                        'reserved' => 0,
                        'available' => $product->quantity,
                        'low_stock_threshold' => $product->low_stock_threshold,
                        'location' => 'Main Warehouse',
                        'track_quantity' => true,
                    ]);
                }
            } else {
                $product->inventory()->delete();
            }
        });
    }

    public static function generateUniqueSlug(string $name): string
    {
        $slug = Str::slug($name);
        $originalSlug = $slug;
        $counter = 1;

        while (self::where('slug', $slug)->exists()) {
            $slug = $originalSlug . '-' . $counter++;
        }

        return $slug;
    }

    public static function generateUniqueSku(): string
    {
        $sku = 'SKU-' . strtoupper(Str::random(8));
        $counter = 1;

        while (self::where('sku', $sku)->exists()) {
            $sku = 'SKU-' . strtoupper(Str::random(8)) . '-' . $counter++;
        }

        return $sku;
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true)->where('is_active', true);
    }

    public function scopeInCategory($query, $categoryId)
    {
        return $query->where('category_id', $categoryId);
    }

    public function scopeByBrand($query, $brandId)
    {
        return $query->where('brand_id', $brandId);
    }

    public function getLowStockAttribute(): bool
    {
        return $this->track_quantity && $this->quantity <= $this->low_stock_threshold;
    }

    public function getInStockAttribute(): bool
    {
        if (! $this->track_quantity) {
            return true;
        }

        return $this->quantity > 0;
    }

    public function getHasVariantsAttribute(): bool
    {
        return $this->variants()->count() > 0;
    }

    public function getMinPriceAttribute(): float
    {
        if ($this->hasVariants) {
            return $this->variants()->min('price') ?? $this->price;
        }

        return $this->price;
    }

    public function getMaxPriceAttribute(): float
    {
        if ($this->hasVariants) {
            return $this->variants()->max('price') ?? $this->price;
        }

        return $this->price;
    }
}
