<?php

namespace Tests\Feature\Api;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->category = Category::factory()->create(['is_active' => true]);
        $this->brand = Brand::factory()->create(['is_active' => true]);
        $this->product = Product::factory()->create([
            'category_id' => $this->category->id,
            'brand_id' => $this->brand->id,
            'price' => 50,
            'quantity' => 100,
            'track_quantity' => true,
            'is_active' => true,
        ]);
        $this->user = User::factory()->create();
        $this->token = $this->user->createToken('api-token')->plainTextToken;
    }

    public function test_authenticated_user_can_list_orders(): void
    {
        Order::factory()->count(3)->create(['user_id' => $this->user->id]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer '.$this->token,
        ])->getJson('/api/orders');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'order_number', 'status', 'total', 'items', 'addresses'],
                ],
                'meta' => ['current_page', 'last_page', 'per_page', 'total'],
            ]);
    }

    public function test_authenticated_user_can_view_own_order(): void
    {
        $order = Order::factory()->create(['user_id' => $this->user->id]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer '.$this->token,
        ])->getJson("/api/orders/{$order->order_number}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => ['id', 'order_number', 'status', 'total', 'items', 'addresses'],
            ]);
    }

    public function test_user_cannot_view_other_users_order(): void
    {
        $otherUser = User::factory()->create();
        $order = Order::factory()->create(['user_id' => $otherUser->id]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer '.$this->token,
        ])->getJson("/api/orders/{$order->order_number}");

        $response->assertStatus(404);
    }

    public function test_authenticated_user_can_create_order(): void
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer '.$this->token,
        ])->postJson('/api/orders', [
            'items' => [
                [
                    'product_id' => $this->product->id,
                    'quantity' => 2,
                ],
            ],
            'shipping_address' => [
                'first_name' => 'John',
                'last_name' => 'Doe',
                'address_line_1' => '123 Main St',
                'city' => 'New York',
                'state' => 'NY',
                'postal_code' => '10001',
                'country' => 'US',
            ],
            'payment_method' => 'credit_card',
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'message',
                'data' => ['id', 'order_number', 'status', 'total', 'items', 'addresses'],
            ]);

        $this->assertDatabaseHas('orders', [
            'user_id' => $this->user->id,
            'status' => 'pending',
            'payment_status' => 'pending',
        ]);

        $this->assertDatabaseHas('order_items', [
            'product_id' => $this->product->id,
            'quantity' => 2,
        ]);
    }

    public function test_order_requires_at_least_one_item(): void
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer '.$this->token,
        ])->postJson('/api/orders', [
            'items' => [],
            'shipping_address' => [
                'first_name' => 'John',
                'last_name' => 'Doe',
                'address_line_1' => '123 Main St',
                'city' => 'New York',
                'state' => 'NY',
                'postal_code' => '10001',
                'country' => 'US',
            ],
            'payment_method' => 'credit_card',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['items']);
    }

    public function test_order_requires_shipping_address(): void
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer '.$this->token,
        ])->postJson('/api/orders', [
            'items' => [
                [
                    'product_id' => $this->product->id,
                    'quantity' => 1,
                ],
            ],
            'payment_method' => 'credit_card',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['shipping_address']);
    }

    public function test_order_cannot_exceed_stock(): void
    {
        $this->product->update(['quantity' => 5, 'track_quantity' => true]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer '.$this->token,
        ])->postJson('/api/orders', [
            'items' => [
                [
                    'product_id' => $this->product->id,
                    'quantity' => 10,
                ],
            ],
            'shipping_address' => [
                'first_name' => 'John',
                'last_name' => 'Doe',
                'address_line_1' => '123 Main St',
                'city' => 'New York',
                'state' => 'NY',
                'postal_code' => '10001',
                'country' => 'US',
            ],
            'payment_method' => 'credit_card',
        ]);

        $response->assertStatus(422)
            ->assertJsonPath('message', 'Not enough stock available')
            ->assertJsonPath('available', 5);
    }

    public function test_order_decreases_stock(): void
    {
        $this->product->update(['quantity' => 100, 'track_quantity' => true]);

        $this->withHeaders([
            'Authorization' => 'Bearer '.$this->token,
        ])->postJson('/api/orders', [
            'items' => [
                [
                    'product_id' => $this->product->id,
                    'quantity' => 3,
                ],
            ],
            'shipping_address' => [
                'first_name' => 'John',
                'last_name' => 'Doe',
                'address_line_1' => '123 Main St',
                'city' => 'New York',
                'state' => 'NY',
                'postal_code' => '10001',
                'country' => 'US',
            ],
            'payment_method' => 'credit_card',
        ]);

        $this->product->refresh();
        $this->assertEquals(97, $this->product->quantity);
    }

    public function test_can_cancel_pending_order(): void
    {
        $order = Order::factory()->create([
            'user_id' => $this->user->id,
            'status' => 'pending',
        ]);
        $order->items()->create([
            'product_id' => $this->product->id,
            'quantity' => 2,
            'price' => $this->product->price,
            'total' => $this->product->price * 2,
            'product_name' => $this->product->name,
            'sku' => $this->product->sku,
        ]);
        $this->product->update(['quantity' => 97, 'track_quantity' => true]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer '.$this->token,
        ])->postJson("/api/orders/{$order->order_number}/cancel");

        $response->assertStatus(200)
            ->assertJsonPath('message', 'Order cancelled successfully');

        $order->refresh();
        $this->assertEquals('cancelled', $order->status);

        $this->product->refresh();
        $this->assertEquals(99, $this->product->quantity);
    }

    public function test_cannot_cancel_shipped_order(): void
    {
        $order = Order::factory()->create([
            'user_id' => $this->user->id,
            'status' => 'shipped',
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer '.$this->token,
        ])->postJson("/api/orders/{$order->order_number}/cancel");

        $response->assertStatus(422)
            ->assertJsonPath('message', 'Order cannot be cancelled');
    }
}
