<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class StoreOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'idempotency_key' => 'nullable|string|max:255',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.variant_id' => 'nullable|exists:product_variants,id',
            'items.*.quantity' => 'required|integer|min:1|max:99',
            'shipping_address' => 'required|array',
            'shipping_address.first_name' => 'required|string|max:255',
            'shipping_address.last_name' => 'required|string|max:255',
            'shipping_address.address_line_1' => 'required|string|max:255',
            'shipping_address.address_line_2' => 'nullable|string|max:255',
            'shipping_address.city' => 'required|string|max:255',
            'shipping_address.state' => 'required|string|max:255',
            'shipping_address.postal_code' => 'required|string|max:20',
            'shipping_address.country' => 'required|string|max:2',
            'shipping_address.phone' => 'nullable|string|max:20',
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
            'coupon_code' => 'nullable|string|max:50',
            'payment_method' => 'required|string',
            'notes' => 'nullable|string',
        ];
    }

    public function messages(): array
    {
        return [
            'items.required' => 'At least one item is required.',
            'items.min' => 'At least one item is required.',
            'items.*.product_id.exists' => 'The selected product does not exist.',
            'items.*.variant_id.exists' => 'The selected variant does not exist.',
            'items.*.quantity.min' => 'Quantity must be at least 1.',
            'items.*.quantity.max' => 'Quantity cannot exceed 99.',
        ];
    }
}
