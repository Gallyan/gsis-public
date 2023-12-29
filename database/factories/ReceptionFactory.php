<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Reception>
 */
class ReceptionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'purchase_id' => fake()->randomElement(\App\Models\Purchase::pluck('id')),
            'subject' => fake()->optional()->sentence(3),
            'number' => fake()->optional()->numberBetween(1, 15),
            'supplier' => fake()->optional()->company,
            'date' => fake()->optional()->dateTimeBetween('-90 days','90 days')?->format('Y-m-d'),
            'amount' => fake()->optional()->randomFloat(2,1,1000),
            'currency' => fake()->randomElement(['EUR', 'USD', 'GBP', 'CHF','BTC']),
            'guests' => [],
        ];
    }
}
