<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class ProductFilterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'category' => 'nullable|string|exists:categories,slug',
            'brand' => 'nullable|string|exists:brands,slug',
            'gender' => 'nullable|string|in:men,women,kids,unisex',
            'season' => 'nullable|string',
            'featured' => 'boolean',
            'on_sale' => 'boolean',
            'min_price' => 'nullable|numeric|min:0',
            'max_price' => 'nullable|numeric|min:0',
            'search' => 'nullable|string|max:255',
            'sort' => 'nullable|string|in:price_asc,price_desc,name_asc,name_desc,oldest,newest',
            'per_page' => 'nullable|integer|min:1|max:100',
        ];
    }
}
