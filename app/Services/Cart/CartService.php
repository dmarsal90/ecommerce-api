<?php

namespace App\Services\Cart;

use App\Models\Product;
use App\Models\ProductVariant;
use App\Services\Contracts\StockServiceInterface;
use Illuminate\Support\Facades\Session;

class CartService
{
    private const CART_SESSION_KEY = 'cart';

    public function __construct(
        private StockServiceInterface $stockService,
    ) {}

    public function getCart(): CartDTO
    {
        $cart = Session::get(self::CART_SESSION_KEY, []);
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

        $cart = Session::get(self::CART_SESSION_KEY, []);
        $key = $this->generateKey($productId, $variantId);

        if (isset($cart[$key])) {
            $newQuantity = $cart[$key]['quantity'] + $quantity;
            $this->validateStock($product, $variant, $newQuantity);
            $cart[$key]['quantity'] = $newQuantity;
        } else {
            $cart[$key] = [
                'product_id' => $productId,
                'variant_id' => $variantId,
                'quantity' => $quantity,
            ];
        }

        Session::put(self::CART_SESSION_KEY, $cart);
        return $this->buildCartDTO($cart);
    }

    public function updateItem(string $key, int $quantity): CartDTO
    {
        $cart = Session::get(self::CART_SESSION_KEY, []);

        if (! isset($cart[$key])) {
            throw new \InvalidArgumentException('Item not found in cart');
        }

        $item = $cart[$key];
        $product = Product::find($item['product_id']);
        $variant = $item['variant_id'] ? ProductVariant::find($item['variant_id']) : null;

        if (! $product || ! $product->is_active) {
            throw new \InvalidArgumentException('Product is no longer available');
        }

        $this->validateStock($product, $variant, $quantity);

        $cart[$key]['quantity'] = $quantity;
        Session::put(self::CART_SESSION_KEY, $cart);
        return $this->buildCartDTO($cart);
    }

    public function removeItem(string $key): CartDTO
    {
        $cart = Session::get(self::CART_SESSION_KEY, []);

        if (! isset($cart[$key])) {
            throw new \InvalidArgumentException('Item not found in cart');
        }

        unset($cart[$key]);
        Session::put(self::CART_SESSION_KEY, $cart);
        return $this->buildCartDTO($cart);
    }

    public function clearCart(): CartDTO
    {
        Session::forget(self::CART_SESSION_KEY);
        return new CartDTO([], 0, 0);
    }

    public function getCount(): int
    {
        $cart = Session::get(self::CART_SESSION_KEY, []);
        return array_sum(array_column($cart, 'quantity'));
    }

    private function validateStock(Product $product, ?ProductVariant $variant, int $quantity): void
    {
        $model = $variant ?? $product;

        if (! $this->stockService->checkStock($model, $quantity)) {
            $available = $this->stockService->getAvailableStock($model);
            throw new \InvalidArgumentException("Not enough stock available. Available: {$available}");
        }
    }

    private function buildCartDTO(array $cart): CartDTO
    {
        $productIds = array_column($cart, 'product_id');
        $variantIds = array_filter(array_column($cart, 'variant_id'));

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
            $variant = $item['variant_id'] ? $variants->get($item['variant_id']) : null;

            if (! $product || ! $product->is_active) {
                continue;
            }

            $price = $variant?->price ?? $product->price;
            $lineTotal = $price * $item['quantity'];
            $subtotal += $lineTotal;
            $totalQuantity += $item['quantity'];

            $items[] = new CartItemDTO(
                key: $key,
                productId: $product->id,
                variantId: $item['variant_id'] ?? null,
                quantity: $item['quantity'],
                price: $price,
                total: $lineTotal,
                product: [
                    'id' => $product->id,
                    'name' => $product->name,
                    'slug' => $product->slug,
                    'sku' => $product->sku,
                    'price' => $product->price,
                    'compare_at_price' => $product->compare_at_price,
                    'images' => $product->images->map(fn ($img) => [
                        'id' => $img->id,
                        'url' => $img->url,
                        'alt' => $img->alt,
                    ])->toArray(),
                ],
                variant: $variant ? [
                    'id' => $variant->id,
                    'sku' => $variant->sku,
                    'price' => $variant->price,
                    'attributes' => $variant->attributeValues->mapWithKeys(fn ($av) => [$av->attribute->name => $av->value])->toArray(),
                ] : null,
            );
        }

        return new CartDTO($items, round($subtotal, 2), $totalQuantity);
    }

    private function generateKey(int $productId, ?int $variantId): string
    {
        return $variantId ? "{$productId}:{$variantId}" : (string) $productId;
    }
}