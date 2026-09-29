<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreOrderRequest;
use App\Http\Resources\Api\OrderResource;
use App\Models\Order;
use App\Models\OrderAddress;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class OrderController extends Controller
{
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
        $validated = $request->validated();

        $user = Auth::user();

        // Validate stock and prepare items before transaction
        $items = [];
        $subtotal = 0;

        foreach ($validated['items'] as $itemData) {
            $product = Product::findOrFail($itemData['product_id']);
            $variant = isset($itemData['variant_id']) ? ProductVariant::findOrFail($itemData['variant_id']) : null;

            if (! $product->is_active || ($variant && ! $variant->is_active)) {
                return response()->json([
                    'message' => 'Product or variant is not available',
                ], 422);
            }

            $quantity = $itemData['quantity'];

            // Check stock with lock
            $available = DB::transaction(function () use ($product, $variant, $quantity) {
                if ($variant) {
                    $lockedVariant = ProductVariant::where('id', $variant->id)
                        ->lockForUpdate()
                        ->first();
                    $avail = $lockedVariant->inventory?->available ?? $lockedVariant->quantity;
                    if ($lockedVariant->track_quantity && $avail < $quantity) {
                        return response()->json([
                            'message' => 'Not enough stock available',
                            'available' => $avail,
                        ], 422);
                    }

                    return $avail;
                }
                $lockedProduct = Product::where('id', $product->id)
                    ->lockForUpdate()
                    ->first();
                $avail = $lockedProduct->inventory?->available ?? $lockedProduct->quantity;
                if ($lockedProduct->track_quantity && $avail < $quantity) {
                    return response()->json([
                        'message' => 'Not enough stock available',
                        'available' => $avail,
                    ], 422);
                }

                return $avail;
            });

            if ($available instanceof JsonResponse) {
                return $available;
            }

            if ($variant) {
                $price = $variant->price;
                $sku = $variant->sku;
                $variantName = $variant->sku;
            } else {
                $price = $product->price;
                $sku = $product->sku;
                $variantName = null;
            }

            $total = $price * $quantity;
            $subtotal += $total;

            $items[] = [
                'product_id' => $product->id,
                'variant_id' => $variant?->id,
                'quantity' => $quantity,
                'price' => $price,
                'compare_at_price' => $variant?->compare_at_price ?? $product->compare_at_price,
                'total' => $total,
                'product_name' => $product->name,
                'variant_name' => $variantName,
                'sku' => $sku,
            ];
        }

        $taxRate = config('shop.tax_rate', 0.08);
        $freeShippingThreshold = config('shop.free_shipping_threshold', 50);
        $shippingCost = config('shop.shipping_cost', 9.99);

        $tax = round($subtotal * $taxRate, 2);
        $shipping = $subtotal > $freeShippingThreshold ? 0 : $shippingCost;
        $discount = 0;
        $total = $subtotal + $tax + $shipping - $discount;

        return DB::transaction(function () use ($user, $validated, $items, $subtotal, $tax, $shipping, $discount, $total) {
            // Create order
            $order = Order::create([
                'user_id' => $user->id,
                'order_number' => 'ORD-'.strtoupper(Str::random(8)),
                'email' => $user->email,
                'phone' => $validated['shipping_address']['phone'] ?? null,
                'status' => 'pending',
                'payment_status' => 'pending',
                'fulfillment_status' => 'unfulfilled',
                'subtotal' => $subtotal,
                'tax' => $tax,
                'shipping' => $shipping,
                'discount' => $discount,
                'total' => $total,
                'currency' => config('shop.currency', 'USD'),
                'payment_method' => $validated['payment_method'],
                'notes' => $validated['notes'] ?? null,
            ]);

            // Create order items
            foreach ($items as $item) {
                OrderItem::create(array_merge($item, ['order_id' => $order->id]));
            }

            // Create shipping address
            OrderAddress::create(array_merge($validated['shipping_address'], [
                'order_id' => $order->id,
                'type' => 'shipping',
            ]));

            // Create billing address
            $billingAddress = $validated['billing_address'] ?? $validated['shipping_address'];
            OrderAddress::create(array_merge($billingAddress, [
                'order_id' => $order->id,
                'type' => 'billing',
            ]));

            // Decrease stock with locks
            foreach ($items as $item) {
                if ($item['variant_id']) {
                    $variant = ProductVariant::where('id', $item['variant_id'])
                        ->lockForUpdate()
                        ->first();
                    if ($variant && $variant->track_quantity) {
                        $variant->decrement('quantity', $item['quantity']);
                        $variant->inventory?->decrement('available', $item['quantity']);
                    }
                } else {
                    $product = Product::where('id', $item['product_id'])
                        ->lockForUpdate()
                        ->first();
                    if ($product && $product->track_quantity) {
                        $product->decrement('quantity', $item['quantity']);
                        $product->inventory?->decrement('available', $item['quantity']);
                    }
                }
            }

            // Clear cart
            session()->forget('cart');

            return response()->json([
                'message' => 'Order created successfully',
                'data' => (new OrderResource($order->load(['items.product.images', 'items.variant.attributeValues.attribute', 'addresses'])))->resolve(),
            ], 201);
        });
    }

    public function cancel(string $orderNumber): JsonResponse
    {
        $user = Auth::user();

        $order = Order::where('user_id', $user->id)
            ->where('order_number', $orderNumber)
            ->firstOrFail();

        if (! in_array($order->status, ['pending', 'processing'])) {
            return response()->json([
                'message' => 'Order cannot be cancelled',
            ], 422);
        }

        if ($order->status === 'cancelled') {
            return response()->json([
                'message' => 'Order is already cancelled',
            ], 422);
        }

        $order->update([
            'status' => 'cancelled',
            'cancelled_at' => now(),
        ]);

        // Restore stock with locks
        foreach ($order->items as $item) {
            if ($item->variant_id) {
                $variant = ProductVariant::where('id', $item->variant_id)
                    ->lockForUpdate()
                    ->first();
                if ($variant && $variant->track_quantity) {
                    $variant->increment('quantity', $item->quantity);
                    $variant->inventory?->increment('available', $item->quantity);
                }
            } else {
                $product = Product::where('id', $item->product_id)
                    ->lockForUpdate()
                    ->first();
                if ($product && $product->track_quantity) {
                    $product->increment('quantity', $item->quantity);
                    $product->inventory?->increment('available', $item->quantity);
                }
            }
        }

        return response()->json([
            'message' => 'Order cancelled successfully',
            'data' => (new OrderResource($order))->resolve(),
        ]);
    }
}
