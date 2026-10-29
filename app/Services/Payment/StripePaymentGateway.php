<?php

namespace App\Services\Payment;

use App\Services\Contracts\PaymentGatewayInterface;
use Stripe\StripeClient;
use Stripe\Webhook;

class StripePaymentGateway implements PaymentGatewayInterface
{
    private ?StripeClient $stripe = null;

    // Built on first use so checkouts that don't touch Stripe work without a secret key.
    private function stripe(): StripeClient
    {
        return $this->stripe ??= new StripeClient(array_filter([
            'api_key' => config('services.stripe.secret'),
            'api_base' => config('services.stripe.api_base'),
        ]));
    }

    public function createPaymentIntent(float $amount, string $currency, array $metadata = [], array $options = []): array
    {
        $params = array_filter([
            'amount' => (int) round($amount * 100), // Convert to cents
            'currency' => strtolower($currency),
            'allowed_payment_method_types' => ['card'],
            // Ask for 3-D Secure whenever the card supports it (liability shift on fraud disputes).
            // shortcut: always on for safety; relax to 'automatic' if conversion suffers.
            'payment_method_options' => ['card' => ['request_three_d_secure' => 'any']],
            'metadata' => $metadata,
            'customer' => $options['customer'] ?? null,
            'payment_method' => $options['payment_method'] ?? null,
            'shipping' => $options['shipping'] ?? null,
        ]);

        $paymentIntent = $this->stripe()->paymentIntents->create(
            $params,
            array_filter(['idempotency_key' => $options['idempotency_key'] ?? null])
        );

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
        $paymentIntent = $this->stripe()->paymentIntents->retrieve($paymentIntentId);

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
            $event = Webhook::constructEvent($payload, $signature, $webhookSecret);

            return [
                'id' => $event->id,
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

        $refund = $this->stripe()->refunds->create($params);

        return [
            'id' => $refund->id,
            'amount' => $refund->amount,
            'currency' => $refund->currency,
            'status' => $refund->status,
        ];
    }

    public function createCustomer(string $email, string $name, array $metadata, string $idempotencyKey): string
    {
        return $this->stripe()->customers->create(
            ['email' => $email, 'name' => $name, 'metadata' => $metadata],
            ['idempotency_key' => $idempotencyKey]
        )->id;
    }

    public function createSetupIntent(string $customerId): array
    {
        $setupIntent = $this->stripe()->setupIntents->create([
            'customer' => $customerId,
            'allowed_payment_method_types' => ['card'],
            'usage' => 'on_session',
        ]);

        return ['id' => $setupIntent->id, 'client_secret' => $setupIntent->client_secret];
    }

    public function retrievePaymentMethod(string $paymentMethodId): array
    {
        $method = $this->stripe()->paymentMethods->retrieve($paymentMethodId);
        $customer = $method->customer;

        return [
            'id' => $method->id,
            'type' => $method->type,
            'customer' => is_object($customer) ? $customer->id : $customer,
            'brand' => $method->card?->brand,
            'last4' => $method->card?->last4,
            'exp_month' => $method->card?->exp_month,
            'exp_year' => $method->card?->exp_year,
            'funding' => $method->card?->funding,
        ];
    }

    public function detachPaymentMethod(string $paymentMethodId): void
    {
        $this->stripe()->paymentMethods->detach($paymentMethodId);
    }

    public function updatePaymentMethodBilling(string $paymentMethodId, array $billing): void
    {
        $this->stripe()->paymentMethods->update($paymentMethodId, ['billing_details' => $billing]);
    }
}
