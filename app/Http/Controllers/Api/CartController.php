<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class CartController extends Controller
{
    public function index(): JsonResponse
    {
        $cart = session()->get('cart', []);
        
        $items = [];
        $subtotal = 0;
        $totalQuantity = 0;

        foreach ($cart as $key => $item) {
            $product = Product::with('images')->find($item['product_id']);
            $variant = null;
            
            if (isset($item['variant_id'])) {
                $variant = ProductVariant::find($item['variant_id']);
            }

            if (!$product || !$product->is_active) {
                continue;
            }

            $price = $variant?->price ?? $product->price;
            $lineTotal = $price * $item['quantity'];
            $subtotal += $lineTotal;
            $totalQuantity += $item['quantity'];

            $items[] = [
                'key' => $key,
                'product_id' => $product->id,
                'variant_id' => $item['variant_id'] ?? null,
                'quantity' => $item['quantity'],
                'price' => $price,
                'total' => $lineTotal,
                'product' => [
                    'id' => $product->id,
                    'name' => $product->name,
                    'slug' => $product->slug,
                    'sku' => $product->sku,
                    'price' => $product->price,
                    'compare_at_price' => $product->compare_at_price,
                    'images' => $product->images->map(function ($img) {
                        return [
                            'id' => $img->id,
                            'url' => $img->url,
                            'alt' => $img->alt,
                        ];
                    }),
                ],
                'variant' => $variant ? [
                    'id' => $variant->id,
                    'sku' => $variant->sku,
                    'price' => $variant->price,
                    'attributes' => $variant->attributeValues->mapWithKeys(function ($av) {
                        return [$av->attribute->name => $av->value];
                    }),
                ] : null,
            ];
        }

        return response()->json([
            'data' => [
                'items' => $items,
                'subtotal' => $subtotal,
                'total_quantity' => $totalQuantity,
                'item_count' => count($items),
            ],
        ]);
    }

    public function add(Request $request): JsonResponse
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'variant_id' => 'nullable|exists:product_variants,id',
            'quantity' => 'required|integer|min:1|max:99',
        ]);

        $product = Product::findOrFail($request->product_id);
        
        if (!$product->is_active) {
            return response()->json([
                'message' => 'Product is not available',
            ], 422);
        }

        $variant = null;
        if ($request->variant_id) {
            $variant = ProductVariant::findOrFail($request->variant_id);
            
            if (!$variant->is_active) {
                return response()->json([
                    'message' => 'Variant is not available',
                ], 422);
            }

            // Check stock
            $available = $variant->inventory?->available ?? $variant->quantity;
            if ($variant->track_quantity && $available < $request->quantity) {
                return response()->json([
                    'message' => 'Not enough stock available',
                    'available' => $available,
                ], 422);
            }
        } else {
            // Check product stock
            $available = $product->inventory?->available ?? $product->quantity;
            if ($product->track_quantity && $available < $request->quantity) {
                return response()->json([
                    'message' => 'Not enough stock available',
                    'available' => $available,
                ], 422);
            }
        }

        $cart = session()->get('cart', []);
        $key = $request->variant_id 
            ? "{$request->product_id}:{$request->variant_id}" 
            : (string)$request->product_id;

        if (isset($cart[$key])) {
            $newQuantity = $cart[$key]['quantity'] + $request->quantity;
            
            // Check stock again for updated quantity
            $available = $variant?->inventory?->available ?? $product->inventory?->available ?? ($variant?->quantity ?? $product->quantity);
            if ($variant?->track_quantity ?? $product->track_quantity) {
                if ($available < $newQuantity) {
                    return response()->json([
                        'message' => 'Not enough stock available',
                        'available' => $available,
                    ], 422);
                }
            }
            
            $cart[$key]['quantity'] = $newQuantity;
        } else {
            $cart[$key] = [
                'product_id' => $request->product_id,
                'variant_id' => $request->variant_id,
                'quantity' => $request->quantity,
            ];
        }

        session()->put('cart', $cart);

        return response()->json([
            'message' => 'Product added to cart',
            'cart_count' => count($cart),
        ]);
    }

    public function update(Request $request, string $key): JsonResponse
    {
        $request->validate([
            'quantity' => 'required|integer|min:1|max:99',
        ]);

        $cart = session()->get('cart', []);

        if (!isset($cart[$key])) {
            return response()->json([
                'message' => 'Item not found in cart',
            ], 404);
        }

        $item = $cart[$key];
        $product = Product::find($item['product_id']);
        $variant = $item['variant_id'] ? ProductVariant::find($item['variant_id']) : null;

        if (!$product || !$product->is_active) {
            return response()->json([
                'message' => 'Product is no longer available',
            ], 422);
        }

        // Check stock
        $available = $variant?->inventory?->available ?? $product->inventory?->available ?? ($variant?->quantity ?? $product->quantity);
        if (($variant?->track_quantity ?? $product->track_quantity) && $available < $request->quantity) {
            return response()->json([
                'message' => 'Not enough stock available',
                'available' => $available,
            ], 422);
        }

        $cart[$key]['quantity'] = $request->quantity;
        session()->put('cart', $cart);

        return response()->json([
            'message' => 'Cart updated',
        ]);
    }

    public function remove(string $key): JsonResponse
    {
        $cart = session()->get('cart', []);

        if (!isset($cart[$key])) {
            return response()->json([
                'message' => 'Item not found in cart',
            ], 404);
        }

        unset($cart[$key]);
        session()->put('cart', $cart);

        return response()->json([
            'message' => 'Item removed from cart',
        ]);
    }

    public function clear(): JsonResponse
    {
        session()->forget('cart');

        return response()->json([
            'message' => 'Cart cleared',
        ]);
    }

    public function count(): JsonResponse
    {
        $cart = session()->get('cart', []);
        $count = array_sum(array_column($cart, 'quantity'));

        return response()->json([
            'count' => $count,
        ]);
    }
}