<?php

namespace Database\Factories;

use App\Models\Order;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Order>
 */
class OrderFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $totalAmount = fake()->numberBetween(25, 200) * 1000;
        $shippingCost = fake()->randomElement([0, 10000, 15000, 20000]);

        return [
            'order_code' => Order::generateOrderCode(),
            'customer_name' => fake()->name(),
            'customer_phone' => '08'.fake()->numerify('##########'),
            'customer_address' => fake()->address(),
            'customer_notes' => fake()->optional()->sentence(),
            'total_amount' => $totalAmount,
            'shipping_cost' => $shippingCost,
            'grand_total' => $totalAmount + $shippingCost,
            'status' => fake()->randomElement(['pending', 'confirmed', 'processing', 'shipped', 'completed']),
            'payment_status' => fake()->randomElement(['unpaid', 'paid']),
        ];
    }
}
