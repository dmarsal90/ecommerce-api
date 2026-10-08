<?php

namespace App\Services\Order;

use App\Models\Coupon;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Support\Facades\Auth;

class CouponService
{
    public function validateCoupon(string $code, float $subtotal, array $productIds = [], array $categoryIds = []): ?Coupon
    {
        $coupon = Coupon::where('code', $code)
            ->validForOrder($subtotal, $productIds, $categoryIds)
            ->first();

        if (! $coupon) {
            return null;
        }

        $user = Auth::user();
        if ($user && ! $coupon->isValidForCustomer($user->id)) {
            return null;
        }

        return $coupon;
    }

    public function applyCoupon(Order $order, Coupon $coupon): float
    {
        $subtotal = $order->subtotal;
        $discount = $coupon->calculateDiscount($subtotal);

        if ($discount <= 0) {
            return 0;
        }

        $order->update([
            'discount' => $discount,
            'total' => $subtotal + $order->tax + $order->shipping - $discount,
            'meta_data' => array_merge($order->meta_data ?? [], [
                'coupon_code' => $coupon->code,
                'coupon_type' => $coupon->type,
                'coupon_value' => $coupon->value,
            ]),
        ]);

        $coupon->incrementUsage();

        return $discount;
    }

    public function getValidCoupons(float $subtotal, array $productIds = [], array $categoryIds = []): \Illuminate\Database\Eloquent\Collection
    {
        return Coupon::validForOrder($subtotal, $productIds, $categoryIds)
            ->orderBy('value', 'desc')
            ->get();
    }
}