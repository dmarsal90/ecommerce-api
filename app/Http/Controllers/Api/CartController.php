<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\UpdateCartQuantityRequest;
use App\Http\Requests\Api\UpdateCartRequest;
use App\Http\Resources\Api\CartResource;
use App\Services\Cart\CartService;
use Illuminate\Http\JsonResponse;
use InvalidArgumentException;

class CartController extends Controller
{
    public function __construct(
        private CartService $cartService,
    ) {}

    public function index(): JsonResponse
    {
        $cart = $this->cartService->getCart();

        return (new CartResource($cart->toArray()))->response();
    }

    public function add(UpdateCartRequest $request): JsonResponse
    {
        $validated = $request->validated();

        try {
            $cart = $this->cartService->addItem(
                $validated['product_id'],
                $validated['quantity'],
                $validated['variant_id'] ?? null
            );

            return response()->json([
                'message' => 'Product added to cart',
                'cart_count' => $cart->items ? count($cart->items) : 0,
            ]);
        } catch (InvalidArgumentException $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    public function update(UpdateCartQuantityRequest $request, int $itemId): JsonResponse
    {
        $validated = $request->validated();

        try {
            $this->cartService->updateItem($itemId, $validated['quantity']);

            return response()->json([
                'message' => 'Cart updated',
            ]);
        } catch (InvalidArgumentException $e) {
            $status = $e->getMessage() === 'Item not found in cart' ? 404 : 422;
            return response()->json([
                'message' => $e->getMessage(),
            ], $status);
        }
    }

    public function remove(int $itemId): JsonResponse
    {
        try {
            $this->cartService->removeItem($itemId);

            return response()->json([
                'message' => 'Item removed from cart',
            ]);
        } catch (InvalidArgumentException $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 404);
        }
    }

    public function clear(): JsonResponse
    {
        $this->cartService->clearCart();

        return response()->json([
            'message' => 'Cart cleared',
        ]);
    }

    public function count(): JsonResponse
    {
        $count = $this->cartService->getCount();

        return response()->json([
            'count' => $count,
        ]);
    }
}
