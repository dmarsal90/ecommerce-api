<?php

namespace App\Services\Cart;

use App\Http\Resources\Api\ProductImageResource;
use App\Models\Cart;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Services\Contracts\StockServiceInterface;
use Illuminate\Support\Facades\Auth;

class CartService
{
    public function __construct(
        private StockServiceInterface $stockService,
    ) {}

    public function getCart(): CartDTO
    {
        $cart = $this->getOrCreateCart();

        return $this->buildCartDTO($cart);
    }

    public function addItem(int $productId, int $quantity, ?int $variantId = null): CartDTO
    {
        $product = Product::findOrFail($productId);
        $variant = $variantId ? ProductVariant::findOrFail($variantId) : null;

        if (! $product->is_active || ($variant && ! $variant->is_active)) {
            throw new \InvalidArgumentException('Product or variant is not available');
        }

        $this->validateStock($product, $variant, $quantity);

        $cart = $this->getOrCreateCart();

        $existingItem = $cart->items()
            ->where('product_id', $productId)
            ->where('variant_id', $variantId)
            ->first();

        if ($existingItem) {
            $newQuantity = $existingItem->quantity + $quantity;
            $this->validateStock($product, $variant, $newQuantity);
            $existingItem->update(['quantity' => $newQuantity]);
        } else {
            $cart->items()->create([
                'product_id' => $productId,
                'variant_id' => $variantId,
                'quantity' => $quantity,
            ]);
        }

        return $this->buildCartDTO($cart->fresh());
    }

    public function updateItem(int $itemId, int $quantity): CartDTO
    {
        $cart = $this->getOrCreateCart();

        $item = $cart->items()->find($itemId);

        if (! $item) {
            throw new \InvalidArgumentException('Item not found in cart');
        }

        $product = Product::find($item->product_id);
        $variant = $item->variant_id ? ProductVariant::find($item->variant_id) : null;

        if (! $product || ! $product->is_active) {
            throw new \InvalidArgumentException('Product is no longer available');
        }

        $this->validateStock($product, $variant, $quantity);

        $item->update(['quantity' => $quantity]);

        return $this->buildCartDTO($cart->fresh());
    }

    public function removeItem(int $itemId): CartDTO
    {
        $cart = $this->getOrCreateCart();

        $item = $cart->items()->find($itemId);

        if (! $item) {
            throw new \InvalidArgumentException('Item not found in cart');
        }

        $item->delete();

        return $this->buildCartDTO($cart->fresh());
    }

    public function clearCart(): CartDTO
    {
        $cart = $this->getOrCreateCart();
        $cart->items()->delete();

        return $this->buildCartDTO($cart->fresh());
    }

    public function getCount(): int
    {
        $cart = $this->getOrCreateCart();

        return $cart->items()->sum('quantity');
    }

    public function mergeGuestCartToUser(int $userId): void
    {
        $guestToken = request()->header('X-Cart-Token');
        if (! $guestToken) {
            return;
        }

        $guestCart = Cart::where('session_id', $guestToken)
            ->whereNull('user_id')
            ->first();

        if (! $guestCart) {
            return;
        }

        $userCart = Cart::firstOrCreate(['user_id' => $userId]);

        foreach ($guestCart->items as $item) {
            $existingItem = $userCart->items()
                ->where('product_id', $item->product_id)
                ->where('variant_id', $item->variant_id)
                ->first();

            if ($existingItem) {
                $existingItem->increment('quantity', $item->quantity);
                $item->delete();
            } else {
                $item->update(['cart_id' => $userCart->id]);
            }
        }

        $guestCart->delete();
    }

    private function getOrCreateCart(): Cart
    {
        // Cart routes are public, so resolve the Bearer token explicitly.
        $user = Auth::guard('sanctum')->user();

        if ($user) {
            return Cart::firstOrCreate(['user_id' => $user->id]);
        }

        return Cart::firstOrCreate(['session_id' => request()->header('X-Cart-Token')]);
    }

    private function validateStock(Product $product, ?ProductVariant $variant, int $quantity): void
    {
        $model = $variant ?? $product;

        if (! $this->stockService->checkStock($model, $quantity)) {
            $available = $this->stockService->getAvailableStock($model);
            throw new \InvalidArgumentException("Not enough stock available. Available: {$available}");
        }
    }

    private function buildCartDTO(Cart $cart): CartDTO
    {
        $items = $cart->items()
            ->with([
                'product.images',
                'variant.attributeValues.attribute',
            ])
            ->get();

        $cartItems = [];
        $subtotal = 0;
        $totalQuantity = 0;

        foreach ($items as $item) {
            $product = $item->product;
            $variant = $item->variant;

            if (! $product || ! $product->is_active) {
                continue;
            }

            $price = $variant?->price ?? $product->price;
            $lineTotal = $price * $item->quantity;
            $subtotal += $lineTotal;
            $totalQuantity += $item->quantity;

            $cartItems[] = new CartItemDTO(
                key: (string) $item->id,
                productId: $product->id,
                variantId: $item->variant_id,
                quantity: $item->quantity,
                price: $price,
                total: $lineTotal,
                product: [
                    'id' => $product->id,
                    'name' => $product->name,
                    'slug' => $product->slug,
                    'sku' => $product->sku,
                    'price' => $product->price,
                    'compare_at_price' => $product->compare_at_price,
                    'images' => ProductImageResource::collection($product->images)->resolve(),
                ],
                variant: $variant ? [
                    'id' => $variant->id,
                    'sku' => $variant->sku,
                    'price' => $variant->price,
                    'attributes' => $variant->attributeValues->mapWithKeys(fn ($av) => [$av->attribute->name => $av->value])->toArray(),
                ] : null,
            );
        }

        return new CartDTO($cartItems, round($subtotal, 2), $totalQuantity);
    }
}
