<?php

namespace App\Services\Contracts;

interface PaymentGatewayInterface
{
    /**
     * Card-only PaymentIntent, confirmed client-side (3-D Secure friendly).
     *
     * @param  array{customer?: string, payment_method?: string, shipping?: array, idempotency_key?: string}  $options
     */
    public function createPaymentIntent(float $amount, string $currency, array $metadata = [], array $options = []): array;

    public function confirmPaymentIntent(string $paymentIntentId): array;

    /** @return array{id: string, type: string, data: array} */
    public function handleWebhook(string $payload, string $signature): array;

    public function refund(string $paymentIntentId, ?float $amount = null): array;

    /** Returns the Stripe customer id. */
    public function createCustomer(string $email, string $name, array $metadata, string $idempotencyKey): string;

    /** @return array{id: string, client_secret: string} */
    public function createSetupIntent(string $customerId): array;

    /** @return array{id: string, type: string, customer: ?string, brand: ?string, last4: ?string, exp_month: ?int, exp_year: ?int, funding: ?string} */
    public function retrievePaymentMethod(string $paymentMethodId): array;

    public function detachPaymentMethod(string $paymentMethodId): void;

    /** @param array{name: string, address: array{line1: string, line2: ?string, city: string, state: string, postal_code: string, country: string}} $billing */
    public function updatePaymentMethodBilling(string $paymentMethodId, array $billing): void;
}
