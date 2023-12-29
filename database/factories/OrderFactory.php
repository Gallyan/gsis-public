<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class OrderFactory extends Factory
{
    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'user_id' => fake()->randomElement(\App\Models\User::pluck('id')),
            'subject' => fake()->sentence(),
            'institution_id' => fake()->randomElement(\App\Models\Institution::pluck('id')),
            'supplier' => fake()->optional()->company(),
            'books' => fake()->optional(default: [])->passthrough(array_map(function () {
                            return [
                                'title' => fake()->sentence(3),
                                'author' => fake()->name,
                                'isbn' => fake()->isbn13,
                                'edition' => fake()->randomElement(array_keys(\App\Models\Order::EDITION)),
                            ];
                        }, range(1,  fake()->numberBetween(1, 5)))),
            'comments' => fake()->optional()->text(500),
            'status' => fake()->randomElement(array_keys(\App\Models\Order::STATUSES)),
            'amount' => fake()->optional()->randomFloat(2,1,10000),
            'created_at' => fake()->dateTimeBetween('-90 days'),
        ];
    }
}
