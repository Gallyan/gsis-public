<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class InstitutionFactory extends Factory
{
    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        $from = fake()->optional()->dateTimeBetween('-2 years','+1 years')?->format('Y-m-d');
        $to = fake()->optional()->dateTimeBetween($from,'+2 years')?->format('Y-m-d');

        return [
            'name' => fake()->company,
            'contract' => fake()->company,
            'allocation' => fake()->regexify('[A-Z0-9]{14}\.[A-Z0-9]{10}\.[A-Z0-9]{4}'),
            'wp' => fake()->boolean,
            'from' => $from,
            'to' => $to,
        ];
    }
}
