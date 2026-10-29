<?php

namespace App\Services\Contracts;

interface PaymentGatewayInterface
{
    public function createPaymentIntent(float $amount, string $currency, array $metadata = []): array;

    public function confirmPaymentIntent(string $paymentIntentId): array;

    public function handleWebhook(string $payload, string $signature): array;

    public function refund(string $paymentIntentId, ?float $amount = null): array;
}
