<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class OrderFactory extends Factory
{
    protected $model = Order::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'order_number' => 'ORD-'.strtoupper(Str::random(8)),
            'email' => $this->faker->safeEmail(),
            'phone' => $this->faker->optional()->phoneNumber(),
            'status' => $this->faker->randomElement(['pending', 'processing', 'shipped', 'delivered', 'cancelled']),
            'payment_status' => $this->faker->randomElement(['pending', 'paid', 'failed', 'refunded']),
            'fulfillment_status' => $this->faker->randomElement(['unfulfilled', 'partial', 'fulfilled']),
            'subtotal' => $this->faker->randomFloat(2, 50, 1000),
            'tax' => $this->faker->randomFloat(2, 4, 80),
            'shipping' => $this->faker->randomFloat(2, 0, 20),
            'discount' => 0,
            'total' => $this->faker->randomFloat(2, 50, 1000),
            'currency' => 'USD',
            'payment_method' => $this->faker->randomElement(['credit_card', 'paypal', 'bank_transfer']),
            'notes' => $this->faker->optional()->sentence(),
        ];
    }
}
