<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductVariantFactory extends Factory
{
    protected $model = ProductVariant::class;

    public function definition(): array
    {
        return [
            'product_id' => Product::factory(),
            'sku' => strtoupper($this->faker->unique()->bothify('VAR-???-####')),
            'price' => $this->faker->randomFloat(2, 10, 500),
            'compare_at_price' => $this->faker->optional(0.3)->randomFloat(2, 100, 1000),
            'quantity' => $this->faker->numberBetween(0, 500),
            'track_quantity' => true,
            'is_active' => true,
        ];
    }
}
