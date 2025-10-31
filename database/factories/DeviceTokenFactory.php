<?php

namespace Database\Factories;

use App\Models\DeviceToken;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\DeviceToken>
 */
class DeviceTokenFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = DeviceToken::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        // Define possible platforms
        $platforms = ['android', 'ios', 'web'];

        return [
            // Ensure a User exists for the foreign key
            'user_id' => User::factory(), 
            // Generate a unique token
            'token' => $this->faker->unique()->sha256(),
            // Randomly select a platform
            'platform' => $this->faker->randomElement($platforms),
            // Set last seen to a recent date
            'last_seen_at' => $this->faker->dateTimeBetween('-1 month', 'now'),
            'is_valid' => true,
        ];
    }

    /**
     * Indicate that the device token is invalid.
     */
    public function invalid(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_valid' => false,
        ]);
    }
}