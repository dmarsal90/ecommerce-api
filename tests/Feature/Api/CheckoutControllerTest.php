<?php

namespace Tests\Feature\Api;

use App\Models\Address;
use App\Models\Order;
use App\Models\PaymentMethod;
use App\Models\Product;
use App\Models\User;
use App\Services\Contracts\PaymentGatewayInterface;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\Support\FakePaymentGateway;
use Tests\TestCase;

class CheckoutControllerTest extends TestCase
{
    use RefreshDatabase;

    private FakePaymentGateway $stripe;

    private User $user;

    private Product $product;

    protected function setUp(): void
    {
        parent::setUp();

        $this->stripe = new FakePaymentGateway;
        $this->app->instance(PaymentGatewayInterface::class, $this->stripe);
        $this->user = User::factory()->create(['stripe_customer_id' => 'cus_1']);
        $this->product = Product::factory()->create(['price' => 50, 'quantity' => 10, 'track_quantity' => true, 'is_active' => true]);
        Sanctum::actingAs($this->user);
    }

    private function card(array $attrs = []): PaymentMethod
    {
        return PaymentMethod::create($attrs + [
            'user_id' => $this->user->id, 'type' => 'card', 'stripe_payment_method_id' => 'pm_1', 'brand' => 'visa',
            'last4' => '4242', 'exp_month' => 12, 'exp_year' => (int) now()->addYears(2)->format('Y'), 'is_default' => true,
        ]);
    }

    private function address(string $type, ?User $owner = null): Address
    {
        return Address::create([
            'user_id' => ($owner ?? $this->user)->id, 'type' => $type, 'first_name' => 'Ada', 'last_name' => 'Lovelace',
            'address_line_1' => '1 Main St', 'city' => 'NYC', 'state' => 'NY', 'postal_code' => '10001', 'country' => 'US',
        ]);
    }

    private function payload(array $extra = []): array
    {
        return $extra + [
            'items' => [['product_id' => $this->product->id, 'quantity' => 1]],
            'payment_method' => 'credit_card',
        ];
    }

    public function test_requirements_walks_card_then_billing_then_shipping(): void
    {
        $this->getJson('/api/checkout/requirements')->assertOk()
            ->assertJsonPath('next_step', 'payment_method')
            ->assertJsonPath('missing', ['payment_method', 'billing_address', 'shipping_address']);

        $this->card(['exp_year' => (int) now()->subYear()->format('Y')]);
        $this->getJson('/api/checkout/requirements')->assertJsonPath('next_step', 'payment_method');

        $this->card(['stripe_payment_method_id' => 'pm_2']);
        $this->address('billing');
        $this->getJson('/api/checkout/requirements')->assertJsonPath('next_step', 'shipping_address');

        $this->address('shipping');
        $this->getJson('/api/checkout/requirements')->assertJsonPath('next_step', 'ready')->assertJsonPath('missing', []);
    }

    public function test_saved_card_with_saved_addresses_creates_a_payment_intent_for_the_server_side_total(): void
    {
        $card = $this->card();
        $billing = $this->address('billing');
        $shipping = $this->address('shipping');

        $this->postJson('/api/checkout/payment-intent', $this->payload([
            'payment_method_id' => $card->id, 'billing_address_id' => $billing->id, 'shipping_address_id' => $shipping->id,
        ]))->assertSuccessful()->assertJsonPath('payment_intent.client_secret', 'pi_1_secret_x');

        $intent = $this->stripe->intents[0];
        $this->assertSame('cus_1', $intent['options']['customer']);
        $this->assertSame('pm_1', $intent['options']['payment_method']);
        $this->assertEqualsWithDelta(Order::first()->total, $intent['amount'], 0.001);
        $this->assertSame(9, $this->product->fresh()->quantity);
    }

    public function test_card_payment_is_rejected_without_a_saved_card_or_billing_address(): void
    {
        $shipping = $this->address('shipping');

        $this->postJson('/api/checkout/payment-intent', $this->payload(['shipping_address_id' => $shipping->id]))
            ->assertUnprocessable()->assertJsonValidationErrors(['payment_method_id', 'billing_address_id']);

        $this->assertSame(0, Order::count());
    }

    public function test_cannot_use_another_users_card_or_address(): void
    {
        $other = User::factory()->create();
        $otherCard = PaymentMethod::create([
            'user_id' => $other->id, 'type' => 'card', 'stripe_payment_method_id' => 'pm_x', 'brand' => 'visa',
            'last4' => '1111', 'exp_month' => 12, 'exp_year' => 2099,
        ]);
        $otherAddress = $this->address('shipping', $other);

        $this->postJson('/api/checkout/payment-intent', $this->payload([
            'payment_method_id' => $otherCard->id, 'billing_address_id' => $otherAddress->id, 'shipping_address_id' => $otherAddress->id,
        ]))->assertUnprocessable()->assertJsonValidationErrors(['payment_method_id', 'billing_address_id', 'shipping_address_id']);
    }

    public function test_expired_card_is_rejected_before_any_order_is_created(): void
    {
        $card = $this->card(['exp_year' => (int) now()->subYear()->format('Y')]);
        $address = $this->address('shipping');

        $this->postJson('/api/checkout/payment-intent', $this->payload([
            'payment_method_id' => $card->id, 'billing_address' => $address->only('first_name', 'last_name', 'address_line_1', 'city', 'state', 'postal_code', 'country'),
            'shipping_address_id' => $address->id,
        ]))->assertUnprocessable();

        $this->assertSame(0, Order::count());
        $this->assertSame([], $this->stripe->intents);
    }

    public function test_raw_card_data_is_refused(): void
    {
        $this->postJson('/api/checkout/payment-intent', $this->payload(['card_number' => '4242424242424242']))
            ->assertUnprocessable()->assertJsonValidationErrors('card_number');
    }

    public function test_orders_endpoint_refuses_card_payments(): void
    {
        $this->postJson('/api/orders', $this->payload(['shipping_address_id' => $this->address('shipping')->id]))
            ->assertUnprocessable();
    }
}
