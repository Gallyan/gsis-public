<?php

namespace Database\Factories;

use App\Models\Institution;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class InstitutionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->company(),
            'contract' => $this->faker->company(),
            'allocation' => strtoupper(Str::random(14).'.'.Str::random(10).'.'.Str::random(4)),
        ];
    }
}
