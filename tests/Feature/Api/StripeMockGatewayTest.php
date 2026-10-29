<?php

namespace Tests\Feature\Api;

use App\Services\Payment\StripePaymentGateway;
use PHPUnit\Framework\Attributes\Group;
use Stripe\Exception\InvalidRequestException;
use Stripe\StripeClient;
use Tests\TestCase;

/**
 * Sends the real Stripe SDK requests to a local stripe-mock, which validates every call against Stripe's
 * OpenAPI spec (unknown or malformed parameters are rejected). Skipped when stripe-mock is not running:
 *   docker run --rm -p 12111:12111 stripe/stripe-mock
 */
#[Group('stripe-mock')]
class StripeMockGatewayTest extends TestCase
{
    private StripePaymentGateway $gateway;

    protected function setUp(): void
    {
        parent::setUp();

        $socket = @fsockopen('127.0.0.1', 12111, $errno, $errstr, 0.3);
        if (! $socket) {
            $this->markTestSkipped('stripe-mock is not running on 127.0.0.1:12111');
        }
        fclose($socket);

        config(['services.stripe.secret' => 'sk_test_123', 'services.stripe.api_base' => 'http://127.0.0.1:12111']);
        $this->gateway = new StripePaymentGateway;
    }

    public function test_customer_setup_intent_and_payment_method_lifecycle_is_spec_valid(): void
    {
        $customer = $this->gateway->createCustomer('ana@example.com', 'Ana', ['user_id' => '1'], 'customer-1');
        $this->assertStringStartsWith('cus_', $customer);

        $setup = $this->gateway->createSetupIntent($customer);
        $this->assertStringStartsWith('seti_', $setup['id']);
        $this->assertNotEmpty($setup['client_secret']);

        $method = $this->gateway->retrievePaymentMethod('pm_123');
        $this->assertSame('card', $method['type']);
        $this->assertNotEmpty($method['last4']);

        $this->gateway->updatePaymentMethodBilling('pm_123', [
            'name' => 'Ana Perez',
            'address' => ['line1' => 'Calle 1', 'line2' => null, 'city' => 'Madrid', 'state' => 'M', 'postal_code' => '28001', 'country' => 'ES'],
        ]);

        $this->gateway->detachPaymentMethod('pm_123');
        $this->addToAssertionCount(1);
    }

    public function test_payment_intent_is_card_only_with_saved_card_customer_and_shipping(): void
    {
        $intent = $this->gateway->createPaymentIntent(119.98, 'USD', ['order_id' => '7', 'order_number' => 'ORD-1'], [
            'customer' => 'cus_123',
            'payment_method' => 'pm_123',
            'shipping' => ['name' => 'Ana Perez', 'address' => ['line1' => 'Calle 1', 'city' => 'Madrid', 'state' => 'M', 'postal_code' => '28001', 'country' => 'ES']],
            'idempotency_key' => 'order-ORD-1',
        ]);

        $this->assertStringStartsWith('pi_', $intent['id']);
        $this->assertNotEmpty($intent['client_secret']);
    }

    public function test_the_mock_really_rejects_non_spec_requests(): void
    {
        $this->expectException(InvalidRequestException::class);

        // Guards the test itself: proves stripe-mock validates parameters, so passing tests mean something.
        (new StripeClient(['api_key' => 'sk_test_123', 'api_base' => 'http://127.0.0.1:12111']))
            ->paymentIntents->create(['amount' => 100, 'currency' => 'usd', 'card_number' => '4242424242424242']);
    }
}
