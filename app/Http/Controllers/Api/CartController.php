<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\UpdateCartQuantityRequest;
use App\Http\Requests\Api\UpdateCartRequest;
use App\Http\Resources\Api\CartResource;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class CartController extends Controller
{
    public function index(): JsonResponse
    {
        $cart = session()->get('cart', []);

        $productIds = [];
        $variantIds = [];

        foreach ($cart as $item) {
            $productIds[] = $item['product_id'];
            if ($item['variant_id'] ?? null) {
                $variantIds[] = $item['variant_id'];
            }
        }

        $products = Product::with('images')
            ->whereIn('id', $productIds)
            ->get()
            ->keyBy('id');

        $variants = ProductVariant::with('attributeValues.attribute')
            ->whereIn('id', $variantIds)
            ->get()
            ->keyBy('id');

        $items = [];
        $subtotal = 0;
        $totalQuantity = 0;

        foreach ($cart as $key => $item) {
            $product = $products->get($item['product_id']);
            $variant = isset($item['variant_id']) ? $variants->get($item['variant_id']) : null;

            if (! $product || ! $product->is_active) {
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

        return (new CartResource([
            'items' => $items,
            'subtotal' => $subtotal,
            'total_quantity' => $totalQuantity,
        ]))->response();
    }

    public function add(UpdateCartRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $product = Product::findOrFail($validated['product_id']);

        if (! $product->is_active) {
            return response()->json([
                'message' => 'Product is not available',
            ], 422);
        }

        $variant = null;
        if ($validated['variant_id'] ?? null) {
            $variant = ProductVariant::findOrFail($validated['variant_id']);

            if (! $variant->is_active) {
                return response()->json([
                    'message' => 'Variant is not available',
                ], 422);
            }

            // Check stock with lock
            $available = DB::transaction(function () use ($variant) {
                $lockedVariant = ProductVariant::where('id', $variant->id)
                    ->lockForUpdate()
                    ->first();

                return $lockedVariant->inventory?->available ?? $lockedVariant->quantity;
            });

            if ($variant->track_quantity && $available < $validated['quantity']) {
                return response()->json([
                    'message' => 'Not enough stock available',
                    'available' => $available,
                ], 422);
            }
        } else {
            // Check product stock with lock
            $available = DB::transaction(function () use ($product) {
                $lockedProduct = Product::where('id', $product->id)
                    ->lockForUpdate()
                    ->first();

                return $lockedProduct->inventory?->available ?? $lockedProduct->quantity;
            });

            if ($product->track_quantity && $available < $validated['quantity']) {
                return response()->json([
                    'message' => 'Not enough stock available',
                    'available' => $available,
                ], 422);
            }
        }

        $cart = session()->get('cart', []);
        $key = ($validated['variant_id'] ?? null)
            ? "{$validated['product_id']}:{$validated['variant_id']}"
            : (string) $validated['product_id'];

        if (isset($cart[$key])) {
            $newQuantity = $cart[$key]['quantity'] + $validated['quantity'];

            // Check stock again for updated quantity with lock
            $available = DB::transaction(function () use ($product, $variant) {
                if ($variant) {
                    $lockedVariant = ProductVariant::where('id', $variant->id)
                        ->lockForUpdate()
                        ->first();

                    return $lockedVariant->inventory?->available ?? $lockedVariant->quantity;
                }
                $lockedProduct = Product::where('id', $product->id)
                    ->lockForUpdate()
                    ->first();

                return $lockedProduct->inventory?->available ?? $lockedProduct->quantity;
            });

            if (($variant?->track_quantity ?? $product->track_quantity) && $available < $newQuantity) {
                return response()->json([
                    'message' => 'Not enough stock available',
                    'available' => $available,
                ], 422);
            }

            $cart[$key]['quantity'] = $newQuantity;
        } else {
            $cart[$key] = [
                'product_id' => $validated['product_id'],
                'variant_id' => $validated['variant_id'] ?? null,
                'quantity' => $validated['quantity'],
            ];
        }

        session()->put('cart', $cart);

        return response()->json([
            'message' => 'Product added to cart',
            'cart_count' => count($cart),
        ]);
    }

    public function update(UpdateCartQuantityRequest $request, string $key): JsonResponse
    {
        $validated = $request->validated();

        $cart = session()->get('cart', []);

        if (! isset($cart[$key])) {
            return response()->json([
                'message' => 'Item not found in cart',
            ], 404);
        }

        $item = $cart[$key];
        $product = Product::find($item['product_id']);
        $variant = $item['variant_id'] ? ProductVariant::find($item['variant_id']) : null;

        if (! $product || ! $product->is_active) {
            return response()->json([
                'message' => 'Product is no longer available',
            ], 422);
        }

        // Check stock with lock
        $available = DB::transaction(function () use ($product, $variant) {
            if ($variant) {
                $lockedVariant = ProductVariant::where('id', $variant->id)
                    ->lockForUpdate()
                    ->first();

                return $lockedVariant->inventory?->available ?? $lockedVariant->quantity;
            }
            $lockedProduct = Product::where('id', $product->id)
                ->lockForUpdate()
                ->first();

            return $lockedProduct->inventory?->available ?? $lockedProduct->quantity;
        });

        if (($variant?->track_quantity ?? $product->track_quantity) && $available < $validated['quantity']) {
            return response()->json([
                'message' => 'Not enough stock available',
                'available' => $available,
            ], 422);
        }

        $cart[$key]['quantity'] = $validated['quantity'];
        session()->put('cart', $cart);

        return response()->json([
            'message' => 'Cart updated',
        ]);
    }

    public function remove(string $key): JsonResponse
    {
        $cart = session()->get('cart', []);

        if (! isset($cart[$key])) {
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
