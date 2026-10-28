<?php

namespace Tests\Feature\Api;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class CartControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Guest carts are identified by this header, as the frontend will send it.
        $this->withHeader('X-Cart-Token', (string) Str::uuid());
    }

    public function test_can_get_empty_cart(): void
    {
        $response = $this->getJson('/api/cart');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => ['items', 'subtotal', 'total_quantity', 'item_count'],
            ]);
        $this->assertEmpty($response->json('data.items'));
        $this->assertEquals(0, $response->json('data.subtotal'));
        $this->assertEquals(0, $response->json('data.total_quantity'));
        $this->assertEquals(0, $response->json('data.item_count'));
    }

    public function test_can_add_product_to_cart(): void
    {
        $category = Category::factory()->create(['is_active' => true]);
        $brand = Brand::factory()->create(['is_active' => true]);
        $product = Product::factory()->create([
            'category_id' => $category->id,
            'brand_id' => $brand->id,
            'price' => 50,
            'quantity' => 100,
            'track_quantity' => true,
            'is_active' => true,
        ]);

        $response = $this->postJson('/api/cart', [
            'product_id' => $product->id,
            'quantity' => 2,
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'message',
                'cart_count',
            ]);
        $this->assertEquals(1, $response->json('cart_count'));
    }

    public function test_can_add_product_variant_to_cart(): void
    {
        $category = Category::factory()->create(['is_active' => true]);
        $brand = Brand::factory()->create(['is_active' => true]);
        $product = Product::factory()->create([
            'category_id' => $category->id,
            'brand_id' => $brand->id,
            'price' => 50,
            'quantity' => 100,
            'track_quantity' => true,
            'is_active' => true,
        ]);
        $variant = ProductVariant::factory()->create([
            'product_id' => $product->id,
            'price' => 60,
            'quantity' => 50,
            'track_quantity' => true,
            'is_active' => true,
        ]);

        $response = $this->postJson('/api/cart', [
            'product_id' => $product->id,
            'variant_id' => $variant->id,
            'quantity' => 1,
        ]);

        $response->assertStatus(200);
        $this->assertEquals(1, $response->json('cart_count'));
    }

    public function test_cannot_add_inactive_product(): void
    {
        $category = Category::factory()->create(['is_active' => true]);
        $brand = Brand::factory()->create(['is_active' => true]);
        $product = Product::factory()->create([
            'category_id' => $category->id,
            'brand_id' => $brand->id,
            'is_active' => false,
        ]);

        $response = $this->postJson('/api/cart', [
            'product_id' => $product->id,
            'quantity' => 1,
        ]);

        $response->assertStatus(422)
            ->assertJsonPath('message', 'Product or variant is not available');
    }

    public function test_cannot_exceed_stock(): void
    {
        $category = Category::factory()->create(['is_active' => true]);
        $brand = Brand::factory()->create(['is_active' => true]);
        $product = Product::factory()->create([
            'category_id' => $category->id,
            'brand_id' => $brand->id,
            'price' => 50,
            'quantity' => 5,
            'track_quantity' => true,
            'is_active' => true,
        ]);

        $response = $this->postJson('/api/cart', [
            'product_id' => $product->id,
            'quantity' => 10,
        ]);

        $response->assertStatus(422)
            ->assertJsonPath('message', 'Not enough stock available. Available: 5');
    }

    public function test_can_update_cart_item_quantity(): void
    {
        $category = Category::factory()->create(['is_active' => true]);
        $brand = Brand::factory()->create(['is_active' => true]);
        $product = Product::factory()->create([
            'category_id' => $category->id,
            'brand_id' => $brand->id,
            'price' => 50,
            'quantity' => 100,
            'track_quantity' => true,
            'is_active' => true,
        ]);

        // Add to cart
        $this->postJson('/api/cart', [
            'product_id' => $product->id,
            'quantity' => 2,
        ]);

        // Get cart to find key
        $cartResponse = $this->getJson('/api/cart');
        $key = $cartResponse->json('data.items.0.key');

        // Update quantity
        $response = $this->putJson("/api/cart/{$key}", [
            'quantity' => 5,
        ]);

        $response->assertStatus(200)
            ->assertJson(['message' => 'Cart updated']);

        // Verify
        $cartResponse = $this->getJson('/api/cart');
        $this->assertEquals(5, $cartResponse->json('data.items.0.quantity'));
    }

    public function test_cannot_update_cart_with_exceeding_stock(): void
    {
        $category = Category::factory()->create(['is_active' => true]);
        $brand = Brand::factory()->create(['is_active' => true]);
        $product = Product::factory()->create([
            'category_id' => $category->id,
            'brand_id' => $brand->id,
            'price' => 50,
            'quantity' => 5,
            'track_quantity' => true,
            'is_active' => true,
        ]);

        $this->postJson('/api/cart', [
            'product_id' => $product->id,
            'quantity' => 2,
        ]);

        $cartResponse = $this->getJson('/api/cart');
        $key = $cartResponse->json('data.items.0.key');

        $response = $this->putJson("/api/cart/{$key}", [
            'quantity' => 10,
        ]);

        $response->assertStatus(422)
            ->assertJsonPath('message', 'Not enough stock available. Available: 5');
    }

    public function test_can_remove_item_from_cart(): void
    {
        $category = Category::factory()->create(['is_active' => true]);
        $brand = Brand::factory()->create(['is_active' => true]);
        $product = Product::factory()->create([
            'category_id' => $category->id,
            'brand_id' => $brand->id,
            'price' => 50,
            'quantity' => 100,
            'is_active' => true,
        ]);

        $this->postJson('/api/cart', [
            'product_id' => $product->id,
            'quantity' => 2,
        ]);

        $cartResponse = $this->getJson('/api/cart');
        $key = $cartResponse->json('data.items.0.key');

        $response = $this->deleteJson("/api/cart/{$key}");

        $response->assertStatus(200)
            ->assertJson(['message' => 'Item removed from cart']);

        $cartResponse = $this->getJson('/api/cart');
        $this->assertEmpty($cartResponse->json('data.items'));
    }

    public function test_can_clear_cart(): void
    {
        $category = Category::factory()->create(['is_active' => true]);
        $brand = Brand::factory()->create(['is_active' => true]);
        $product = Product::factory()->create([
            'category_id' => $category->id,
            'brand_id' => $brand->id,
            'price' => 50,
            'quantity' => 100,
            'is_active' => true,
        ]);

        $this->postJson('/api/cart', [
            'product_id' => $product->id,
            'quantity' => 2,
        ]);

        $response = $this->deleteJson('/api/cart');

        $response->assertStatus(200)
            ->assertJson(['message' => 'Cart cleared']);

        $cartResponse = $this->getJson('/api/cart');
        $this->assertEmpty($cartResponse->json('data.items'));
    }

    public function test_can_get_cart_count(): void
    {
        $category = Category::factory()->create(['is_active' => true]);
        $brand = Brand::factory()->create(['is_active' => true]);
        $product = Product::factory()->create([
            'category_id' => $category->id,
            'brand_id' => $brand->id,
            'price' => 50,
            'quantity' => 100,
            'is_active' => true,
        ]);

        $this->postJson('/api/cart', [
            'product_id' => $product->id,
            'quantity' => 3,
        ]);

        $response = $this->getJson('/api/cart/count');

        $response->assertStatus(200)
            ->assertJsonPath('count', 3);
    }
}
