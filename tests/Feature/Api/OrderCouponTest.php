<?php

namespace Tests\Feature\Api;

use App\Models\Coupon;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\TestResponse;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class OrderCouponTest extends TestCase
{
    use RefreshDatabase;

    private Product $product;

    protected function setUp(): void
    {
        parent::setUp();

        $this->product = Product::factory()->create(['price' => 50, 'quantity' => 100, 'track_quantity' => true, 'is_active' => true]);
        Sanctum::actingAs(User::factory()->create());
    }

    private function order(?string $coupon): TestResponse
    {
        return $this->postJson('/api/orders', [
            'items' => [['product_id' => $this->product->id, 'quantity' => 2]],
            'shipping_address' => [
                'first_name' => 'John', 'last_name' => 'Doe', 'address_line_1' => '123 Main St',
                'city' => 'New York', 'state' => 'NY', 'postal_code' => '10001', 'country' => 'US',
            ],
            'payment_method' => 'bank_transfer',
            'coupon_code' => $coupon,
        ]);
    }

    private function coupon(array $attributes = []): Coupon
    {
        return Coupon::create($attributes + ['code' => 'SAVE10', 'name' => 'Save', 'type' => 'percentage', 'value' => 10, 'is_active' => true]);
    }

    public function test_percentage_coupon_reduces_total_and_counts_usage(): void
    {
        $coupon = $this->coupon();

        $this->order('SAVE10')->assertCreated();

        // subtotal 100 + tax 8 + free shipping - 10% discount
        $this->assertDatabaseHas('orders', ['discount' => 10, 'total' => 98]);
        $this->assertSame(1, $coupon->fresh()->used_count);
    }

    public function test_free_shipping_coupon_waives_shipping_below_threshold(): void
    {
        $this->product->update(['price' => 10]); // subtotal 20, under the free-shipping threshold
        $this->coupon(['code' => 'SHIPFREE', 'type' => 'free_shipping', 'value' => 0]);

        $this->order('SHIPFREE')->assertCreated();

        $this->assertDatabaseHas('orders', ['shipping' => 0, 'discount' => 0, 'total' => 21.6]);
    }

    public function test_unknown_coupon_rejects_order_without_creating_it(): void
    {
        $this->order('NOPE')->assertUnprocessable();

        $this->assertDatabaseCount('orders', 0);
    }

    public function test_usage_limit_is_enforced(): void
    {
        $this->coupon(['usage_limit' => 1]);

        $this->order('SAVE10')->assertCreated();
        $this->order('SAVE10')->assertUnprocessable();

        $this->assertDatabaseCount('orders', 1);
    }
}
