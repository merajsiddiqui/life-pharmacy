<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class OrderFactory extends Factory
{
    protected $model = Order::class;

    public function definition()
    {
        return [
            'user_id' => User::factory(),
            'total_amount' => $this->faker->randomFloat(2, 100, 10000),
            'status' => $this->faker->randomElement(['pending', 'processing', 'completed', 'cancelled']),
            'shipping_address' => $this->faker->address,
            'billing_address' => $this->faker->address,
            'payment_method' => $this->faker->randomElement(['credit_card', 'debit_card', 'cash_on_delivery']),
            'payment_status' => $this->faker->randomElement(['pending', 'paid', 'failed']),
        ];
    }
} 