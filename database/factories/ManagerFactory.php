<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/* Call tinker with App\Models\Manager::factory()->count(5)->create(); to associate 5 managers to elements */

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Manager>
 */
class ManagerFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        // Object that can be associated with documents
        $object_type = fake()->randomElement([
            \App\Models\Order::class,
            \App\Models\Purchase::class,
        ]);

        return [
            'user_id' => fake()->randomElement(\App\Models\User::role('manager')->pluck('id')),
            'manageable_id' => $object_type::pluck('id')->random(),
            'manageable_type' => $object_type,
        ];
    }
}
