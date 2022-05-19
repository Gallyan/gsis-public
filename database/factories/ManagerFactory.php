<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\Order;
use Illuminate\Database\Eloquent\Factories\Factory;

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
    public function definition()
    {

        // Select a mananger
        $manager_id = User::role('manager')->get()->random(1)->pluck('id')->first();

        // Object that can be associated with documents
        $object = $this->faker->randomElement([
            Order::class,
        ]);

        // Object id
        if ( $object === 'App\Models\Order' ) {
            $id = Order::all()->random(1)->pluck('id')->first();
        }

        return [
            'user_id' => $manager_id,
            'manageable_id' => $id,
            'manageable_type' => $object,
        ];
    }
}
