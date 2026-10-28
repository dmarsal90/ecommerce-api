<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Coupon extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'code',
        'name',
        'description',
        'type',
        'value',
        'min_order_amount',
        'max_discount_amount',
        'usage_limit',
        'usage_limit_per_customer',
        'used_count',
        'starts_at',
        'expires_at',
        'is_active',
        'applicable_categories',
        'applicable_products',
        'excluded_categories',
        'excluded_products',
        'customer_groups',
    ];

    protected $casts = [
        'value' => 'decimal:2',
        'min_order_amount' => 'decimal:2',
        'max_discount_amount' => 'decimal:2',
        'usage_limit' => 'integer',
        'usage_limit_per_customer' => 'integer',
        'used_count' => 'integer',
        'starts_at' => 'date',
        'expires_at' => 'date',
        'is_active' => 'boolean',
        'applicable_categories' => 'array',
        'applicable_products' => 'array',
        'excluded_categories' => 'array',
        'excluded_products' => 'array',
        'customer_groups' => 'array',
    ];

    public function scopeActive($query)
    {
        return $query->where('is_active', true)
            ->where(function ($q) {
                $q->whereNull('starts_at')->orWhere('starts_at', '<=', now());
            })
            ->where(function ($q) {
                $q->whereNull('expires_at')->orWhere('expires_at', '>=', now());
            })
            ->where(function ($q) {
                $q->whereNull('usage_limit')->orWhere('used_count', '<', 'usage_limit');
            });
    }

    public function scopeValidForOrder($query, float $subtotal, array $productIds = [], array $categoryIds = [], ?int $customerGroupId = null)
    {
        return $query->active()
            ->where(function ($q) use ($subtotal) {
                $q->whereNull('min_order_amount')->orWhere('min_order_amount', '<=', $subtotal);
            })
            ->where(function ($q) use ($productIds) {
                if (empty($productIds)) {
                    return;
                }
                $q->where(function ($subQ) use ($productIds) {
                    $subQ->whereNull('applicable_products')
                        ->orWhereJsonContains('applicable_products', $productIds);
                });
            })
            ->where(function ($q) use ($categoryIds) {
                if (empty($categoryIds)) {
                    return;
                }
                $q->where(function ($subQ) use ($categoryIds) {
                    $subQ->whereNull('applicable_categories')
                        ->orWhereJsonContains('applicable_categories', $categoryIds);
                });
            })
            ->where(function ($q) use ($productIds) {
                if (empty($productIds)) {
                    return;
                }
                $q->where(function ($subQ) use ($productIds) {
                    $subQ->whereNull('excluded_products')
                        ->orWhereJsonDoesntContain('excluded_products', $productIds);
                });
            })
            ->where(function ($q) use ($categoryIds) {
                if (empty($categoryIds)) {
                    return;
                }
                $q->where(function ($subQ) use ($categoryIds) {
                    $subQ->whereNull('excluded_categories')
                        ->orWhereJsonDoesntContain('excluded_categories', $categoryIds);
                });
            })
            ->where(function ($q) use ($customerGroupId) {
                if (! $customerGroupId) {
                    return;
                }
                $q->where(function ($subQ) use ($customerGroupId) {
                    $subQ->whereNull('customer_groups')
                        ->orWhereJsonContains('customer_groups', $customerGroupId);
                });
            });
    }

    public function calculateDiscount(float $subtotal): float
    {
        $discount = match ($this->type) {
            'percentage' => round($subtotal * ($this->value / 100), 2),
            'fixed_amount' => min($this->value, $subtotal),
            'free_shipping' => 0, // handled separately in shipping calculation
            default => 0,
        };

        if ($this->max_discount_amount && $discount > $this->max_discount_amount) {
            $discount = $this->max_discount_amount;
        }

        return min($discount, $subtotal);
    }

    public function isValidForCustomer(int $customerId): bool
    {
        if (! $this->usage_limit_per_customer) {
            return true;
        }

        // Check how many times this customer has used this coupon
        $usageCount = Order::where('user_id', $customerId)
            ->whereJsonContains('meta_data->coupon_code', $this->code)
            ->count();

        return $usageCount < $this->usage_limit_per_customer;
    }

    public function incrementUsage(): void
    {
        $this->increment('used_count');
    }
}
