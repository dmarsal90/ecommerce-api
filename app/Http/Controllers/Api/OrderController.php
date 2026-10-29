<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreOrderRequest;
use App\Http\Resources\Api\OrderResource;
use App\Models\Order;
use App\Services\Order\OrderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use InvalidArgumentException;

class OrderController extends Controller
{
    public function __construct(
        private OrderService $orderService,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $user = Auth::user();

        $query = Order::with(['items.product.images', 'items.variant.attributeValues.attribute', 'addresses'])
            ->where('user_id', $user->id)
            ->orderBy('created_at', 'desc');

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        $perPage = min($request->get('per_page', 20), 100);
        $orders = $query->paginate($perPage);

        return response()->json([
            'data' => OrderResource::collection($orders)->resolve(),
            'meta' => [
                'current_page' => $orders->currentPage(),
                'last_page' => $orders->lastPage(),
                'per_page' => $orders->perPage(),
                'total' => $orders->total(),
            ],
        ]);
    }

    public function show(string $orderNumber): JsonResponse
    {
        $user = Auth::user();

        $order = Order::with([
            'items.product.images',
            'items.variant.attributeValues.attribute',
            'addresses',
        ])->where('user_id', $user->id)
            ->where('order_number', $orderNumber)
            ->firstOrFail();

        return (new OrderResource($order))->response();
    }

    public function store(StoreOrderRequest $request): JsonResponse
    {
        if ($request->isCard()) {
            return response()->json(['message' => 'Card payments must be started from /checkout/payment-intent'], 422);
        }

        $validated = $request->checkoutData();

        try {
            $order = $this->orderService->createFromCart($validated);

            return response()->json([
                'message' => 'Order created successfully',
                'data' => (new OrderResource($order))->resolve(),
            ], 201);
        } catch (InvalidArgumentException $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    public function cancel(string $orderNumber): JsonResponse
    {
        $user = Auth::user();

        $order = Order::where('user_id', $user->id)
            ->where('order_number', $orderNumber)
            ->firstOrFail();

        try {
            $order = $this->orderService->cancelOrder($order);

            return response()->json([
                'message' => 'Order cancelled successfully',
                'data' => (new OrderResource($order))->resolve(),
            ]);
        } catch (InvalidArgumentException $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 422);
        }
    }
}
