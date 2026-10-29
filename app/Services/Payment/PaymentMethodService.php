<?php

namespace App\Services\Payment;

use App\Models\PaymentMethod;
use App\Models\User;
use App\Services\Contracts\PaymentGatewayInterface;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use Stripe\Exception\InvalidRequestException;

class PaymentMethodService
{
    public const MAX_PER_USER = 10;

    public function __construct(private PaymentGatewayInterface $gateway) {}

    public function ensureCustomer(User $user): string
    {
        if ($user->stripe_customer_id) {
            return $user->stripe_customer_id;
        }

        // Row lock + Stripe idempotency key: concurrent requests end up with one customer.
        return DB::transaction(function () use ($user) {
            $locked = User::whereKey($user->id)->lockForUpdate()->firstOrFail();

            if (! $locked->stripe_customer_id) {
                $locked->forceFill([
                    'stripe_customer_id' => $this->gateway->createCustomer(
                        $locked->email, $locked->name, ['user_id' => (string) $locked->id], "customer-{$locked->id}"
                    ),
                ])->save();
            }

            $user->stripe_customer_id = $locked->stripe_customer_id;

            return $locked->stripe_customer_id;
        });
    }

    public function setupIntent(User $user): array
    {
        return $this->gateway->createSetupIntent($this->ensureCustomer($user));
    }

    /**
     * Saves a card the client already confirmed with Stripe.js. Everything is re-read from Stripe:
     * the client only supplies an id, never card data, and the card must belong to this user's customer.
     */
    public function store(User $user, string $stripePaymentMethodId): PaymentMethod
    {
        try {
            $info = $this->gateway->retrievePaymentMethod($stripePaymentMethodId);
        } catch (InvalidRequestException) {
            throw new InvalidArgumentException('Invalid payment method');
        }

        // One generic message for every rejection so the endpoint cannot be used as an oracle.
        if ($info['type'] !== 'card'
            || ! $user->stripe_customer_id
            || $info['customer'] !== $user->stripe_customer_id
            || ! $info['last4'] || ! $info['exp_month'] || ! $info['exp_year']) {
            throw new InvalidArgumentException('Invalid payment method');
        }

        $method = new PaymentMethod([
            'user_id' => $user->id,
            'stripe_payment_method_id' => $info['id'],
            'brand' => $info['brand'],
            'last4' => $info['last4'],
            'exp_month' => $info['exp_month'],
            'exp_year' => $info['exp_year'],
            'funding' => $info['funding'],
        ]);

        if ($method->isExpired()) {
            throw new InvalidArgumentException('This card has expired');
        }

        return DB::transaction(function () use ($user, $method) {
            $existing = PaymentMethod::where('stripe_payment_method_id', $method->stripe_payment_method_id)->first();
            if ($existing) {
                return $existing->user_id === $user->id ? $existing : throw new InvalidArgumentException('Invalid payment method');
            }

            $count = $user->paymentMethods()->lockForUpdate()->count();
            if ($count >= self::MAX_PER_USER) {
                throw new InvalidArgumentException('You have reached the maximum number of saved cards');
            }

            $method->is_default = $count === 0;
            $method->save();

            return $method;
        });
    }

    public function setDefault(User $user, PaymentMethod $method): PaymentMethod
    {
        DB::transaction(function () use ($user, $method) {
            $user->paymentMethods()->update(['is_default' => false]);
            $method->update(['is_default' => true]);
        });

        return $method->refresh();
    }

    public function destroy(User $user, PaymentMethod $method): void
    {
        try {
            $this->gateway->detachPaymentMethod($method->stripe_payment_method_id);
        } catch (InvalidRequestException) {
            // Already detached on Stripe's side: still remove it locally.
        }

        DB::transaction(function () use ($user, $method) {
            $wasDefault = $method->is_default;
            $method->delete();

            if ($wasDefault) {
                $user->paymentMethods()->oldest()->first()?->update(['is_default' => true]);
            }
        });
    }
}
