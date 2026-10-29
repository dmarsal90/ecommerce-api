<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class StorePaymentMethodRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'payment_method_id' => ['required', 'string', 'regex:/^pm_[A-Za-z0-9_]{3,64}$/'],
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
}
