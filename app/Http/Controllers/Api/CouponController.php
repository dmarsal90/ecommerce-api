<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\ValidateCouponRequest;
use App\Http\Resources\Api\CouponResource;
use App\Services\Cart\CartService;
use App\Services\Order\CouponService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class CouponController extends Controller
{
    public function __construct(
        private CouponService $couponService,
    ) {}

    public function validate(ValidateCouponRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $user = Auth::user();
        $cart = app(CartService::class)->getCart();

        $productIds = $cart->items ? array_column($cart->items, 'product_id') : [];
        // Category IDs would need to be fetched from products

        $coupon = $this->couponService->validateCoupon(
            $validated['code'],
            $cart->subtotal,
            $productIds
        );

        if (! $coupon) {
            return response()->json([
                'valid' => false,
                'message' => 'Coupon is invalid or expired',
            ], 422);
        }

        $discount = $coupon->calculateDiscount($cart->subtotal);

        return response()->json([
            'valid' => true,
            'coupon' => (new CouponResource($coupon))->resolve(),
            'discount' => $discount,
            'new_subtotal' => $cart->subtotal - $discount,
        ]);
    }

    public function index(): JsonResponse
    {
        $user = Auth::user();
        $cart = app(CartService::class)->getCart();

        $productIds = $cart->items ? array_column($cart->items, 'product_id') : [];

        $coupons = $this->couponService->getValidCoupons(
            $cart->subtotal,
            $productIds
        );

        return CouponResource::collection($coupons)->response();
    }
}
