<?php

namespace Tests\Feature\Api;

use App\Services\Payment\StripePaymentGateway;
use InvalidArgumentException;
use Tests\TestCase;

class StripePaymentGatewayTest extends TestCase
{
    private const SECRET = 'whsec_test_secret';

    private string $payload = '{"id":"evt_1","object":"event","type":"payment_intent.succeeded","data":{"object":{"id":"pi_1","object":"payment_intent","metadata":{"order_id":"7"}}}}';

    protected function setUp(): void
    {
        parent::setUp();

        config(['services.stripe.webhook_secret' => self::SECRET]);
    }

    private function signature(string $payload, string $secret = self::SECRET): string
    {
        $timestamp = time();

        return "t={$timestamp},v1=".hash_hmac('sha256', "{$timestamp}.{$payload}", $secret);
    }

    public function test_valid_signature_returns_event_type_and_array_payload(): void
    {
        $event = (new StripePaymentGateway)->handleWebhook($this->payload, $this->signature($this->payload));

        $this->assertSame('payment_intent.succeeded', $event['type']);
        $this->assertSame('pi_1', $event['data']['id']);
        $this->assertSame('7', $event['data']['metadata']['order_id']);
    }

    public function test_signature_made_with_another_secret_is_rejected(): void
    {
        $this->expectException(InvalidArgumentException::class);

        (new StripePaymentGateway)->handleWebhook($this->payload, $this->signature($this->payload, 'whsec_other'));
    }

    public function test_empty_signature_is_rejected(): void
    {
        $this->expectException(InvalidArgumentException::class);

        (new StripePaymentGateway)->handleWebhook($this->payload, '');
    }
}
