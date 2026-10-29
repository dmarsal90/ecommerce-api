<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreOrderRequest;
use App\Http\Resources\Api\AddressResource;
use App\Http\Resources\Api\OrderResource;
use App\Http\Resources\Api\PaymentMethodResource;
use App\Services\Order\CheckoutService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use InvalidArgumentException;

class CheckoutController extends Controller
{
    public function __construct(
        private CheckoutService $checkoutService,
    ) {}

    /** What the checkout wizard needs next: card, then billing address, then delivery address. */
    public function requirements(Request $request): JsonResponse
    {
        $user = $request->user();
        $cards = $user->paymentMethods()->orderByDesc('is_default')->latest()->get();
        $addresses = $user->addresses()->orderByDesc('is_default')->latest()->get();

        $missing = array_values(array_filter([
            $cards->contains(fn ($card) => ! $card->isExpired()) ? null : 'payment_method',
            $addresses->where('type', 'billing')->isNotEmpty() ? null : 'billing_address',
            $addresses->where('type', 'shipping')->isNotEmpty() ? null : 'shipping_address',
        ]));

        return response()->json([
            'payment_methods' => PaymentMethodResource::collection($cards)->resolve(),
            'billing_addresses' => AddressResource::collection($addresses->where('type', 'billing')->values())->resolve(),
            'shipping_addresses' => AddressResource::collection($addresses->where('type', 'shipping')->values())->resolve(),
            'missing' => $missing,
            'next_step' => $missing[0] ?? 'ready',
        ]);
    }

    public function createPaymentIntent(StoreOrderRequest $request): JsonResponse
    {
        $validated = $request->checkoutData();

        try {
            $result = $this->checkoutService->createCheckoutSession($validated);

            return response()->json([
                'message' => 'Checkout session created',
                'order' => (new OrderResource($result['order']))->resolve(),
                'payment_intent' => $result['payment_intent'],
            ], 201);
        } catch (InvalidArgumentException $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 422);
        }
    }
}
