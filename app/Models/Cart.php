<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Cart extends Model
{
    protected $fillable = [
        'user_id',
        'session_id',
    ];

    public function items(): HasMany
    {
        return $this->hasMany(CartItem::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public static function getCurrentCart(): self
    {
        $user = auth()->user();

        if ($user) {
            return self::firstOrCreate(['user_id' => $user->id]);
        }

        $sessionId = session()->getId();
        if (! $sessionId) {
            session()->start();
            $sessionId = session()->getId();
        }

        return self::firstOrCreate(['session_id' => $sessionId]);
    }

    public function mergeWithUserCart(int $userId): void
    {
        $userCart = self::firstOrCreate(['user_id' => $userId]);

        foreach ($this->items as $item) {
            $existingItem = $userCart->items()
                ->where('product_id', $item->product_id)
                ->where('variant_id', $item->variant_id)
                ->first();

            if ($existingItem) {
                $existingItem->increment('quantity', $item->quantity);
                $item->delete();
            } else {
                $item->update(['cart_id' => $userCart->id]);
            }
        }

        $this->delete();
    }
}
