<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Order>
 */
class OrderFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Order::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        // Define the possible statuses based on your project brief
        $statuses = ['placed', 'processing', 'shipped', 'delivered', 'cancelled'];

        return [
            // Ensure the order is owned by a User (uses UserFactory implicitly)
            'user_id' => User::factory(), 
            
            'code' => 'ORD-' . $this->faker->unique()->numberBetween(1000, 9999),
            
            // Use decimal for amounts
            'amount_decimal' => $this->faker->randomFloat(2, 10, 500), 
            
            'status' => $this->faker->randomElement($statuses),
            
            'placed_at' => $this->faker->dateTimeBetween('-1 year', 'now'),
        ];
    }
}