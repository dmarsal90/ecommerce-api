<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|string|email|max:255|unique:users,email,'.$this->user()?->id,
            'locale' => ['sometimes', Rule::in(config('app.supported_locales'))],
            'current_password' => 'required_with:email|string|min:8',
            'password' => 'sometimes|string|min:8|confirmed',
        ];
    }
}
