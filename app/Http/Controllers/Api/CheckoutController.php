<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreOrderRequest;
use App\Http\Resources\Api\OrderResource;
use App\Services\Order\CheckoutService;
use Illuminate\Http\JsonResponse;
use InvalidArgumentException;

class CheckoutController extends Controller
{
    public function __construct(
        private CheckoutService $checkoutService,
    ) {}

    public function createPaymentIntent(StoreOrderRequest $request): JsonResponse
    {
        $validated = $request->validated();

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