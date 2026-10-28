<?php

namespace Tests\Feature\Api;

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class CartTokenTest extends TestCase
{
    use RefreshDatabase;

    private function add(Product $product, ?string $cartToken = null, ?string $bearer = null)
    {
        $headers = array_filter(['X-Cart-Token' => $cartToken, 'Authorization' => $bearer ? "Bearer {$bearer}" : null]);

        return $this->postJson('/api/cart', ['product_id' => $product->id, 'quantity' => 1], $headers);
    }

    public function test_guest_cart_persists_across_requests_via_cart_token(): void
    {
        $product = Product::factory()->create(['is_active' => true, 'quantity' => 10, 'track_quantity' => true]);

        $token = $this->add($product)->assertOk()->headers->get('X-Cart-Token');

        $this->assertTrue(Str::isUuid($token));
        $this->getJson('/api/cart/count', ['X-Cart-Token' => $token])->assertJson(['count' => 1]);
        $this->getJson('/api/cart/count', ['X-Cart-Token' => (string) Str::uuid()])->assertJson(['count' => 0]);
    }

    public function test_malformed_cart_token_is_replaced(): void
    {
        $response = $this->getJson('/api/cart/count', ['X-Cart-Token' => 'not-a-uuid']);

        $this->assertTrue(Str::isUuid($response->headers->get('X-Cart-Token')));
    }

    public function test_logged_in_user_gets_a_user_cart_and_guest_items_merge_on_login(): void
    {
        $product = Product::factory()->create(['is_active' => true, 'quantity' => 10, 'track_quantity' => true]);
        $user = User::factory()->create(['password' => 'secret-pass-1']);
        $guestToken = $this->add($product)->headers->get('X-Cart-Token');

        $bearer = $this->postJson('/api/login', ['email' => $user->email, 'password' => 'secret-pass-1'], ['X-Cart-Token' => $guestToken])
            ->assertOk()->json('token');

        $this->assertDatabaseHas('carts', ['user_id' => $user->id]);
        $this->getJson('/api/cart/count', ['Authorization' => "Bearer {$bearer}"])->assertJson(['count' => 1]);
        $this->assertDatabaseMissing('carts', ['session_id' => $guestToken]);
    }
}
