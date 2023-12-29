<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Purchase>
 */
class PurchaseFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $institution = \App\Models\Institution::inRandomOrder()->first();

        return [
            'user_id' => fake()->randomElement(\App\Models\User::pluck('id')),
            'subject' => fake()->sentence(),
            'institution_id' => $institution->id,
            'wp' => $institution->wp ? fake()->numberBetween(1, 15) : null,
            'miscs' => fake()->optional(default: [])->passthrough(array_map(function () {
                            return [
                                'subject' => fake()->sentence(3),
                                'supplier' => fake()->company,
                                'date' => fake()->dateTimeBetween('-90 days','90 days')->format('Y-m-d'),
                                'miscamount' => fake()->randomFloat(2,1,1000),
                                'currency' => fake()->randomElement(['EUR', 'USD', 'GBP', 'CHF','BTC']),
                            ];
                        }, range(1, fake()->numberBetween(1, 5)))),
            'comments' => fake()->optional()->text(500),
            'status' => fake()->randomElement(array_keys(\App\Models\Purchase::STATUSES)),
            'amount' => fake()->optional()->randomFloat(2,1,10000),
            'created_at' => fake()->dateTimeBetween('-90 days'),
        ];
    }
}
