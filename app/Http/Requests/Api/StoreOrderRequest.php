<?php

namespace App\Http\Requests\Api;

use App\Models\Address;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreOrderRequest extends FormRequest
{
    public const CARD_METHODS = ['credit_card', 'stripe'];

    private const ADDRESS_FIELDS = ['first_name', 'last_name', 'address_line_1', 'address_line_2', 'city', 'state', 'postal_code', 'country', 'phone'];

    public function authorize(): bool
    {
        return true;
    }

    public function isCard(): bool
    {
        return in_array($this->input('payment_method'), self::CARD_METHODS, true);
    }

    public function rules(): array
    {
        $userId = $this->user()?->id;
        $ownAddress = Rule::exists('addresses', 'id')->where('user_id', $userId);

        return [
            'idempotency_key' => 'nullable|string|max:255',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.variant_id' => 'nullable|exists:product_variants,id',
            'items.*.quantity' => 'required|integer|min:1|max:99',

            // Saved address (id) or an inline one. Ids are always scoped to the authenticated user.
            'shipping_address_id' => ['nullable', 'integer', $ownAddress],
            'shipping_address' => 'required_without:shipping_address_id|array',
            'shipping_address.first_name' => 'required_without:shipping_address_id|string|max:255',
            'shipping_address.last_name' => 'required_without:shipping_address_id|string|max:255',
            'shipping_address.address_line_1' => 'required_without:shipping_address_id|string|max:255',
            'shipping_address.address_line_2' => 'nullable|string|max:255',
            'shipping_address.city' => 'required_without:shipping_address_id|string|max:255',
            'shipping_address.state' => 'required_without:shipping_address_id|string|max:255',
            'shipping_address.postal_code' => 'required_without:shipping_address_id|string|max:20',
            'shipping_address.country' => 'required_without:shipping_address_id|string|max:2',
            'shipping_address.phone' => 'nullable|string|max:20',

            // Card payments need a billing address; other methods default to the shipping one.
            'billing_address_id' => array_filter(['nullable', 'integer', $ownAddress, $this->isCard() ? 'required_without:billing_address' : null]),
            'billing_address' => 'nullable|array',
            'billing_address.first_name' => 'required_with:billing_address|string|max:255',
            'billing_address.last_name' => 'required_with:billing_address|string|max:255',
            'billing_address.address_line_1' => 'required_with:billing_address|string|max:255',
            'billing_address.address_line_2' => 'nullable|string|max:255',
            'billing_address.city' => 'required_with:billing_address|string|max:255',
            'billing_address.state' => 'required_with:billing_address|string|max:255',
            'billing_address.postal_code' => 'required_with:billing_address|string|max:20',
            'billing_address.country' => 'required_with:billing_address|string|max:2',
            'billing_address.phone' => 'nullable|string|max:20',

            'payment_method' => ['required', Rule::in([...self::CARD_METHODS, 'bank_transfer'])],
            'payment_method_id' => ['nullable', 'integer', Rule::exists('payment_methods', 'id')->where('user_id', $userId), $this->isCard() ? 'required' : 'prohibited'],
            'coupon_code' => 'nullable|string|max:50',
            'notes' => 'nullable|string',

            // Raw card data must never reach this server: only Stripe.js may handle it.
            'number' => 'prohibited',
            'card_number' => 'prohibited',
            'card' => 'prohibited',
            'cvc' => 'prohibited',
            'cvv' => 'prohibited',
            'exp_month' => 'prohibited',
            'exp_year' => 'prohibited',
        ];
    }

    /**
     * Validated input with saved-address ids expanded. Prices, totals and amounts are never read from
     * the client: only fields declared above survive validation.
     */
    public function checkoutData(): array
    {
        $data = $this->validated();

        foreach (['shipping', 'billing'] as $type) {
            if (! empty($data["{$type}_address_id"])) {
                $data["{$type}_address"] = Address::where('user_id', $this->user()->id)
                    ->findOrFail($data["{$type}_address_id"])
                    ->only(self::ADDRESS_FIELDS);
            }
        }

        return $data;
    }
}
