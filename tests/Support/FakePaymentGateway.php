<?php

namespace Tests\Support;

use App\Services\Contracts\PaymentGatewayInterface;
use Stripe\Exception\InvalidRequestException;

/** In-memory Stripe stand-in that enforces the rules the app relies on (a card belongs to one customer). */
class FakePaymentGateway implements PaymentGatewayInterface
{
    public array $customers = [];

    /** @var array<string, array> */
    public array $methods = [];

    public array $detached = [];

    public array $billingUpdates = [];

    public array $intents = [];

    public int $customerCalls = 0;

    /** Simulates Stripe.js confirming a SetupIntent in the browser: the card ends up attached to the customer. */
    public function browserSavesCard(string $customerId, string $id = 'pm_card1', array $card = []): string
    {
        $this->methods[$id] = $card + [
            'id' => $id, 'type' => 'card', 'customer' => $customerId, 'brand' => 'visa', 'last4' => '4242',
            'exp_month' => 12, 'exp_year' => (int) now()->addYears(3)->format('Y'), 'funding' => 'credit',
        ];

        return $id;
    }

    public function createCustomer(string $email, string $name, array $metadata, string $idempotencyKey): string
    {
        $this->customerCalls++;

        return $this->customers[$idempotencyKey] ??= 'cus_'.substr(md5($idempotencyKey), 0, 14);
    }

    public function createSetupIntent(string $customerId): array
    {
        return ['id' => 'seti_1', 'client_secret' => 'seti_1_secret_x'];
    }

    public function retrievePaymentMethod(string $paymentMethodId): array
    {
        return $this->methods[$paymentMethodId]
            ?? throw InvalidRequestException::factory("No such PaymentMethod: '{$paymentMethodId}'", 404, null, null, null, 'resource_missing');
    }

    public function detachPaymentMethod(string $paymentMethodId): void
    {
        $this->detached[] = $paymentMethodId;
        unset($this->methods[$paymentMethodId]);
    }

    public function updatePaymentMethodBilling(string $paymentMethodId, array $billing): void
    {
        $this->billingUpdates[$paymentMethodId] = $billing;
    }

    public function createPaymentIntent(float $amount, string $currency, array $metadata = [], array $options = []): array
    {
        $id = 'pi_'.(count($this->intents) + 1);
        $this->intents[] = compact('id', 'amount', 'currency', 'metadata', 'options');

        return ['id' => $id, 'client_secret' => $id.'_secret_x', 'amount' => (int) round($amount * 100), 'currency' => $currency, 'status' => 'requires_confirmation'];
    }

    public function confirmPaymentIntent(string $paymentIntentId): array
    {
        return ['id' => $paymentIntentId, 'status' => 'succeeded', 'amount' => 0, 'currency' => 'usd'];
    }

    public function handleWebhook(string $payload, string $signature): array
    {
        throw new \InvalidArgumentException('not used in this fake');
    }

    public function refund(string $paymentIntentId, ?float $amount = null): array
    {
        return ['id' => 're_1', 'amount' => 0, 'currency' => 'usd', 'status' => 'succeeded'];
    }
}
