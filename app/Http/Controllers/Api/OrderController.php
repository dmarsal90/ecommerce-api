<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderAddress;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Inventory;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
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
            'data' => $orders->items(),
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
            'addresses'
        ])->where('user_id', $user->id)
            ->where('order_number', $orderNumber)
            ->firstOrFail();

        return response()->json(['data' => $order]);
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.variant_id' => 'nullable|exists:product_variants,id',
            'items.*.quantity' => 'required|integer|min:1|max:99',
            'shipping_address' => 'required|array',
            'shipping_address.first_name' => 'required|string|max:255',
            'shipping_address.last_name' => 'required|string|max:255',
            'shipping_address.address_line_1' => 'required|string|max:255',
            'shipping_address.address_line_2' => 'nullable|string|max:255',
            'shipping_address.city' => 'required|string|max:255',
            'shipping_address.state' => 'required|string|max:255',
            'shipping_address.postal_code' => 'required|string|max:20',
            'shipping_address.country' => 'required|string|max:2',
            'shipping_address.phone' => 'nullable|string|max:20',
            'billing_address' => 'nullable|array',
            'billing_address.first_name' => 'required|string|max:255',
            'billing_address.last_name' => 'required|string|max:255',
            'billing_address.address_line_1' => 'required|string|max:255',
            'billing_address.address_line_2' => 'nullable|string|max:255',
            'billing_address.city' => 'required|string|max:255',
            'billing_address.state' => 'required|string|max:255',
            'billing_address.postal_code' => 'required|string|max:20',
            'billing_address.country' => 'required|string|max:2',
            'billing_address.phone' => 'nullable|string|max:20',
            'payment_method' => 'required|string',
            'notes' => 'nullable|string',
        ]);

        $user = Auth::user();

        return DB::transaction(function () use ($request, $user) {
            $items = [];
            $subtotal = 0;

            // Validate stock and prepare items
            foreach ($request->items as $itemData) {
                $product = Product::findOrFail($itemData['product_id']);
                $variant = isset($itemData['variant_id']) ? ProductVariant::findOrFail($itemData['variant_id']) : null;

                if (!$product->is_active || ($variant && !$variant->is_active)) {
                    throw new \Exception('Product or variant is not available');
                }

                $quantity = $itemData['quantity'];
                
                // Check stock
                if ($variant) {
                    $available = $variant->inventory?->available ?? $variant->quantity;
                    if ($variant->track_quantity && $available < $quantity) {
                        throw new \Exception("Not enough stock for {$product->name} ({$variant->sku}). Available: {$available}");
                    }
                    $price = $variant->price;
                    $sku = $variant->sku;
                    $variantName = $variant->sku;
                } else {
                    $available = $product->inventory?->available ?? $product->quantity;
                    if ($product->track_quantity && $available < $quantity) {
                        throw new \Exception("Not enough stock for {$product->name}. Available: {$available}");
                    }
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

            $tax = round($subtotal * 0.08, 2);
            $shipping = $subtotal > 50 ? 0 : 9.99;
            $discount = 0;
            $total = $subtotal + $tax + $shipping - $discount;

            // Create order
            $order = Order::create([
                'user_id' => $user->id,
                'order_number' => 'ORD-' . strtoupper(Str::random(8)),
                'email' => $user->email,
                'phone' => $request->shipping_address['phone'] ?? null,
                'status' => 'pending',
                'payment_status' => 'pending',
                'fulfillment_status' => 'unfulfilled',
                'subtotal' => $subtotal,
                'tax' => $tax,
                'shipping' => $shipping,
                'discount' => $discount,
                'total' => $total,
                'currency' => 'USD',
                'payment_method' => $request->payment_method,
                'notes' => $request->notes,
            ]);

            // Create order items
            foreach ($items as $item) {
                OrderItem::create(array_merge($item, ['order_id' => $order->id]));
            }

            // Create shipping address
            OrderAddress::create(array_merge($request->shipping_address, [
                'order_id' => $order->id,
                'type' => 'shipping',
            ]));

            // Create billing address
            $billingAddress = $request->billing_address ?? $request->shipping_address;
            OrderAddress::create(array_merge($billingAddress, [
                'order_id' => $order->id,
                'type' => 'billing',
            ]));

            // Decrease stock
            foreach ($items as $item) {
                if ($item['variant_id']) {
                    $variant = ProductVariant::find($item['variant_id']);
                    if ($variant && $variant->track_quantity) {
                        $variant->decrement('quantity', $item['quantity']);
                        $variant->inventory?->decrement('available', $item['quantity']);
                    }
                } else {
                    $product = Product::find($item['product_id']);
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
                'data' => $order->load(['items.product.images', 'items.variant.attributeValues.attribute', 'addresses']),
            ], 201);
        });
    }

    public function cancel(string $orderNumber): JsonResponse
    {
        $user = Auth::user();
        
        $order = Order::where('user_id', $user->id)
            ->where('order_number', $orderNumber)
            ->firstOrFail();

        if (!in_array($order->status, ['pending', 'processing'])) {
            return response()->json([
                'message' => 'Order cannot be cancelled',
            ], 422);
        }

        $order->update([
            'status' => 'cancelled',
            'cancelled_at' => now(),
        ]);

        // Restore stock
        foreach ($order->items as $item) {
            if ($item->variant_id) {
                $variant = ProductVariant::find($item->variant_id);
                if ($variant && $variant->track_quantity) {
                    $variant->increment('quantity', $item->quantity);
                    $variant->inventory?->increment('available', $item->quantity);
                }
            } else {
                $product = Product::find($item->product_id);
                if ($product && $product->track_quantity) {
                    $product->increment('quantity', $item->quantity);
                    $product->inventory?->increment('available', $item->quantity);
                }
            }
        }

        return response()->json([
            'message' => 'Order cancelled successfully',
            'data' => $order,
        ]);
    }
}