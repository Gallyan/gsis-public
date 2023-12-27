<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class InstitutionFactory extends Factory
{
    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        $from = (bool) random_int(0, 1) ? fake()->dateTimeBetween('-2 years','+1 years')->format('Y-m-d') : null;

        while( !isset($to) || ( !is_null($from) && $to<$from) ) {
            $to = (bool) random_int(0, 1)
                ? fake()->dateTimeBetween($from,'+2 years')->format('Y-m-d')
                : null;
        }

        return [
            'name' => fake()->company(),
            'contract' => fake()->company(),
            'allocation' => strtoupper(Str::random(14).'.'.Str::random(10).'.'.Str::random(4)),
            'wp' => random_int(0, 1),
            'from' => $from,
            'to' => $to,
        ];
    }
}
