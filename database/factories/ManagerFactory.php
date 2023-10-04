<?php

namespace Database\Factories;

use App\Models\Manager;
use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/* Call tinker with App\Models\Manager::factory()->count(5)->create(); to associate 5 managers to elements */

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Manager>
 */
class ManagerFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Manager::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {

        // Select a manager
        $manager_id = User::role('manager')->get()->random(1)->pluck('id')->first();

        // Object that can be associated with documents
        $object = $this->faker->randomElement([
            Order::class,
        ]);

        // Object id
        if ($object === 'App\Models\Order') {
            $id = Order::all()->random(1)->pluck('id')->first();
        }

        return [
            'user_id' => $manager_id,
            'manageable_id' => $id,
            'manageable_type' => $object,
        ];
    }
}
