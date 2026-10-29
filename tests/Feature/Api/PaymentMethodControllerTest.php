<?php

namespace Tests\Feature\Api;

use App\Models\PaymentMethod;
use App\Models\User;
use App\Services\Contracts\PaymentGatewayInterface;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Laravel\Sanctum\Sanctum;
use Stripe\Exception\ApiConnectionException;
use Tests\Support\FakePaymentGateway;
use Tests\TestCase;

class PaymentMethodControllerTest extends TestCase
{
    use RefreshDatabase;

    private FakePaymentGateway $stripe;

    protected function setUp(): void
    {
        parent::setUp();

        $this->stripe = new FakePaymentGateway;
        $this->app->instance(PaymentGatewayInterface::class, $this->stripe);
    }

    /** Runs the real flow: setup intent (creates the customer) -> browser confirms -> API saves the card. */
    private function saveCard(User $user, string $id = 'pm_card1', array $card = [])
    {
        Sanctum::actingAs($user);
        $this->postJson('/api/payment-methods/setup-intent')->assertOk();
        $this->stripe->browserSavesCard($user->fresh()->stripe_customer_id, $id, $card);

        return $this->postJson('/api/payment-methods', ['payment_method_id' => $id]);
    }

    public function test_every_payment_method_route_requires_authentication(): void
    {
        $this->getJson('/api/payment-methods')->assertUnauthorized();
        $this->postJson('/api/payment-methods/setup-intent')->assertUnauthorized();
        $this->postJson('/api/payment-methods', ['payment_method_id' => 'pm_x1234'])->assertUnauthorized();
        $this->putJson('/api/payment-methods/1/default')->assertUnauthorized();
        $this->deleteJson('/api/payment-methods/1')->assertUnauthorized();
    }

    public function test_setup_intent_returns_only_the_client_secret_and_reuses_one_customer(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $first = $this->postJson('/api/payment-methods/setup-intent')->assertOk()->assertExactJson(['client_secret' => 'seti_1_secret_x']);
        $this->postJson('/api/payment-methods/setup-intent')->assertOk();

        $this->assertNotNull($user->fresh()->stripe_customer_id);
        $this->assertSame(1, count($this->stripe->customers));
        $this->assertSame('no-store, private', $first->headers->get('Cache-Control'));
    }

    public function test_saving_a_card_stores_display_data_only_and_first_card_is_default(): void
    {
        $user = User::factory()->create();

        $response = $this->saveCard($user)->assertCreated();

        $response->assertJson(['data' => ['brand' => 'visa', 'last4' => '4242', 'is_default' => true]]);
        $body = $response->getContent();
        $this->assertStringNotContainsString('pm_card1', $body);
        $this->assertStringNotContainsString($user->fresh()->stripe_customer_id, $body);
        $this->assertSame(['id', 'user_id', 'type', 'stripe_payment_method_id', 'brand', 'last4', 'exp_month', 'exp_year', 'funding', 'is_default', 'created_at', 'updated_at'], Schema::getColumnListing('payment_methods'));
    }

    public function test_a_card_attached_to_someone_elses_customer_is_rejected(): void
    {
        $victim = User::factory()->create();
        $this->saveCard($victim, 'pm_victim');

        $attacker = User::factory()->create();
        Sanctum::actingAs($attacker);
        $this->postJson('/api/payment-methods/setup-intent')->assertOk();

        $this->postJson('/api/payment-methods', ['payment_method_id' => 'pm_victim'])
            ->assertUnprocessable()->assertJson(['message' => 'Invalid payment method']);
        $this->assertSame(0, $attacker->paymentMethods()->count());
    }

    public function test_unknown_unattached_and_non_card_methods_get_the_same_generic_error(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);
        $this->postJson('/api/payment-methods/setup-intent');
        $this->stripe->methods['pm_loose'] = ['id' => 'pm_loose', 'type' => 'card', 'customer' => null, 'brand' => 'visa', 'last4' => '1111', 'exp_month' => 1, 'exp_year' => 2099, 'funding' => 'credit'];
        $this->stripe->methods['pm_sepa'] = ['id' => 'pm_sepa', 'type' => 'sepa_debit', 'customer' => $user->fresh()->stripe_customer_id, 'brand' => null, 'last4' => null, 'exp_month' => null, 'exp_year' => null, 'funding' => null];

        foreach (['pm_doesnotexist', 'pm_loose', 'pm_sepa'] as $id) {
            $this->postJson('/api/payment-methods', ['payment_method_id' => $id])
                ->assertUnprocessable()->assertExactJson(['message' => 'Invalid payment method']);
        }
    }

    public function test_malformed_ids_and_raw_card_data_are_refused_before_reaching_stripe(): void
    {
        Sanctum::actingAs(User::factory()->create());

        foreach (["pm_x'; DROP TABLE users;--", 'tok_visa', str_repeat('a', 300), ['pm_1'], '', 'pm_'] as $bad) {
            $this->postJson('/api/payment-methods', ['payment_method_id' => $bad])->assertUnprocessable();
        }

        $this->postJson('/api/payment-methods', [
            'payment_method_id' => 'pm_abc123', 'number' => '4242424242424242', 'cvc' => '123', 'exp_month' => 12, 'exp_year' => 2030,
        ])->assertUnprocessable()->assertJsonValidationErrors(['number', 'cvc', 'exp_month', 'exp_year']);

        $this->assertDatabaseCount('payment_methods', 0);
        $this->assertSame([], $this->stripe->customers);
    }

    public function test_expired_cards_and_the_per_user_limit_are_enforced(): void
    {
        $user = User::factory()->create();
        $this->saveCard($user, 'pm_old_card', ['exp_year' => (int) now()->subYear()->format('Y')])
            ->assertUnprocessable()->assertJson(['message' => 'This card has expired']);

        foreach (range(1, 9) as $i) {
            PaymentMethod::create(['user_id' => $user->id, 'stripe_payment_method_id' => "pm_seed{$i}", 'brand' => 'visa', 'last4' => '4242', 'exp_month' => 12, 'exp_year' => 2099]);
        }

        $this->postJson('/api/payment-methods', ['payment_method_id' => 'pm_tenth_card'])->assertUnprocessable(); // not known to Stripe
        $this->stripe->browserSavesCard($user->fresh()->stripe_customer_id, 'pm_tenth_card');
        $this->postJson('/api/payment-methods', ['payment_method_id' => 'pm_tenth_card'])->assertCreated();

        $this->stripe->browserSavesCard($user->fresh()->stripe_customer_id, 'pm_eleventh_card');
        $this->postJson('/api/payment-methods', ['payment_method_id' => 'pm_eleventh_card'])
            ->assertUnprocessable()->assertJson(['message' => 'You have reached the maximum number of saved cards']);
        $this->assertSame(10, $user->paymentMethods()->count());
    }

    public function test_saving_the_same_card_twice_is_idempotent(): void
    {
        $user = User::factory()->create();
        $first = $this->saveCard($user)->assertCreated()->json('data.id');
        $second = $this->postJson('/api/payment-methods', ['payment_method_id' => 'pm_card1'])->json('data.id');

        $this->assertSame($first, $second);
        $this->assertSame(1, $user->paymentMethods()->count());
    }

    public function test_users_cannot_read_change_or_delete_each_others_cards(): void
    {
        $owner = User::factory()->create();
        $id = $this->saveCard($owner)->json('data.id');

        $other = User::factory()->create();
        Sanctum::actingAs($other);

        $this->getJson('/api/payment-methods')->assertOk()->assertJsonCount(0, 'data');
        $this->putJson("/api/payment-methods/{$id}/default")->assertNotFound();
        $this->deleteJson("/api/payment-methods/{$id}")->assertNotFound();
        $this->assertSame([], $this->stripe->detached);
        $this->assertDatabaseHas('payment_methods', ['id' => $id]);
    }

    public function test_default_switches_and_deleting_the_default_promotes_another_and_detaches_in_stripe(): void
    {
        $user = User::factory()->create();
        $a = $this->saveCard($user, 'pm_card_a')->json('data.id');
        $b = $this->saveCard($user, 'pm_card_b')->json('data.id');

        $this->putJson("/api/payment-methods/{$b}/default")->assertOk()->assertJsonPath('data.is_default', true);
        $this->assertSame([$b], $user->paymentMethods()->where('is_default', true)->pluck('id')->all());

        $this->deleteJson("/api/payment-methods/{$b}")->assertOk();
        $this->assertSame(['pm_card_b'], $this->stripe->detached);
        $this->assertSame([$a], $user->paymentMethods()->where('is_default', true)->pluck('id')->all());
    }

    public function test_stripe_secrets_do_not_leak_through_the_user_endpoints(): void
    {
        $user = User::factory()->create();
        $this->saveCard($user);

        $this->getJson('/api/user')->assertOk()->assertJsonMissingPath('stripe_customer_id');
        $this->getJson('/api/me')->assertOk();
        $this->assertStringNotContainsString('cus_', $this->getJson('/api/me')->getContent());
    }

    public function test_payment_endpoints_are_rate_limited_against_card_testing(): void
    {
        Sanctum::actingAs(User::factory()->create());

        for ($i = 0; $i < 10; $i++) {
            $this->postJson('/api/payment-methods', ['payment_method_id' => "pm_try{$i}"])->assertUnprocessable();
        }

        $this->postJson('/api/payment-methods', ['payment_method_id' => 'pm_try11'])->assertTooManyRequests();
    }

    public function test_stripe_failures_are_masked_and_logged_without_details(): void
    {
        $this->app->instance(PaymentGatewayInterface::class, new class extends FakePaymentGateway
        {
            public function createSetupIntent(string $customerId): array
            {
                throw new ApiConnectionException('secret-internal-detail sk_live_123');
            }
        });
        Sanctum::actingAs(User::factory()->create());

        $response = $this->postJson('/api/payment-methods/setup-intent')->assertStatus(502);

        $this->assertStringNotContainsString('sk_live', $response->getContent());
        $this->assertStringNotContainsString('secret-internal', $response->getContent());
    }
}
