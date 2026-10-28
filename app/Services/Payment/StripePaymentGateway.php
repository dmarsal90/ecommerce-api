<?php

namespace App\Services\Payment;

use App\Services\Contracts\PaymentGatewayInterface;
use Stripe\StripeClient;

class StripePaymentGateway implements PaymentGatewayInterface
{
    private StripeClient $stripe;

    public function __construct()
    {
        $this->stripe = new StripeClient(config('services.stripe.secret'));
    }

    public function createPaymentIntent(float $amount, string $currency, array $metadata = []): array
    {
        $paymentIntent = $this->stripe->paymentIntents->create([
            'amount' => (int) round($amount * 100), // Convert to cents
            'currency' => strtolower($currency),
            'automatic_payment_methods' => ['enabled' => true],
            'metadata' => $metadata,
        ]);

        return [
            'id' => $paymentIntent->id,
            'client_secret' => $paymentIntent->client_secret,
            'amount' => $paymentIntent->amount,
            'currency' => $paymentIntent->currency,
            'status' => $paymentIntent->status,
        ];
    }

    public function confirmPaymentIntent(string $paymentIntentId): array
    {
        $paymentIntent = $this->stripe->paymentIntents->retrieve($paymentIntentId);

        return [
            'id' => $paymentIntent->id,
            'status' => $paymentIntent->status,
            'amount' => $paymentIntent->amount,
            'currency' => $paymentIntent->currency,
        ];
    }

    public function handleWebhook(string $payload, string $signature): array
    {
        $webhookSecret = config('services.stripe.webhook_secret');

        try {
            $event = $this->stripe->webhooks->constructEvent($payload, $signature, $webhookSecret);

            return [
                'type' => $event->type,
                'data' => $event->data->object->toArray(),
            ];
        } catch (\Exception $e) {
            throw new \InvalidArgumentException('Invalid webhook signature: '.$e->getMessage());
        }
    }

    public function refund(string $paymentIntentId, ?float $amount = null): array
    {
        $params = ['payment_intent' => $paymentIntentId];
        if ($amount !== null) {
            $params['amount'] = (int) round($amount * 100);
        }

        $refund = $this->stripe->refunds->create($params);

        return [
            'id' => $refund->id,
            'amount' => $refund->amount,
            'currency' => $refund->currency,
            'status' => $refund->status,
        ];
    }
}
