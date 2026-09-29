<?php

namespace Database\Factories;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition(): array
    {
        $name = $this->faker->unique()->words(3, true);

        return [
            'category_id' => Category::factory(),
            'brand_id' => Brand::factory(),
            'name' => $name,
            'slug' => Str::slug($name).'-'.$this->faker->unique()->numberBetween(1000, 9999),
            'sku' => strtoupper($this->faker->unique()->bothify('SKU-???-####')),
            'description' => $this->faker->optional()->paragraphs(3, true),
            'short_description' => $this->faker->optional()->sentence(),
            'price' => $this->faker->randomFloat(2, 10, 500),
            'compare_at_price' => $this->faker->optional(0.3)->randomFloat(2, 100, 1000),
            'quantity' => $this->faker->numberBetween(0, 1000),
            'track_quantity' => true,
            'is_active' => true,
            'is_featured' => $this->faker->boolean(10),
            'gender' => $this->faker->optional()->randomElement(['men', 'women', 'kids', 'unisex']),
            'season' => $this->faker->optional()->randomElement(['spring', 'summer', 'autumn', 'winter']),
        ];
    }
}
