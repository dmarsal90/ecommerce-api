<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PaymentMethod extends Model
{
    protected $fillable = [
        'user_id', 'type', 'stripe_payment_method_id', 'brand', 'last4', 'exp_month', 'exp_year', 'funding', 'is_default',
    ];

    protected $hidden = ['stripe_payment_method_id'];

    protected function casts(): array
    {
        return ['is_default' => 'boolean', 'exp_month' => 'integer', 'exp_year' => 'integer'];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function isExpired(): bool
    {
        return now()->startOfMonth()->gt(now()->setDate($this->exp_year, $this->exp_month, 1)->startOfMonth());
    }
}
