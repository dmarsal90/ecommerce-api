<?php

namespace Tests\Feature\Api;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ReviewControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_index_lists_only_approved_reviews_without_exposing_emails(): void
    {
        $product = Product::factory()->create(['is_active' => true]);
        $user = User::factory()->create(['name' => 'Ana']);
        Review::create(['product_id' => $product->id, 'user_id' => $user->id, 'rating' => 5, 'status' => 'approved']);
        Review::create(['product_id' => $product->id, 'user_id' => User::factory()->create()->id, 'rating' => 1, 'status' => 'pending']);

        $response = $this->getJson("/api/products/{$product->slug}/reviews")->assertOk();

        $response->assertJsonCount(1, 'data')->assertJsonPath('data.0.author', 'Ana');
        $this->assertStringNotContainsString($user->email, $response->getContent());
    }

    public function test_store_requires_authentication_and_valid_rating(): void
    {
        $product = Product::factory()->create(['is_active' => true]);

        $this->postJson("/api/products/{$product->slug}/reviews", ['rating' => 5])->assertUnauthorized();

        Sanctum::actingAs(User::factory()->create());
        $this->postJson("/api/products/{$product->slug}/reviews", ['rating' => 9])->assertUnprocessable();
    }

    public function test_store_creates_pending_review_once_and_marks_verified_purchase(): void
    {
        $product = Product::factory()->create(['is_active' => true]);
        $user = User::factory()->create();
        $order = Order::factory()->create(['user_id' => $user->id, 'payment_status' => 'paid']);
        OrderItem::factory()->create(['order_id' => $order->id, 'product_id' => $product->id]);
        Sanctum::actingAs($user);

        $this->postJson("/api/products/{$product->slug}/reviews", ['rating' => 4, 'comment' => 'Great'])
            ->assertCreated();

        $this->assertDatabaseHas('reviews', [
            'product_id' => $product->id, 'user_id' => $user->id, 'status' => 'pending', 'is_verified_purchase' => true,
        ]);
        $this->postJson("/api/products/{$product->slug}/reviews", ['rating' => 5])->assertUnprocessable();
    }
}
