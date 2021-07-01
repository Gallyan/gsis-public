<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\Institution;
use Illuminate\Database\Eloquent\Factories\Factory;

class OrderFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Order::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $institutions = Institution::all()->pluck('id');

        return [
            'subject' => $this->faker->sentence(),
            'institution_id' => $institutions[mt_rand(0,count($institutions)-1)],
            'supplier' => mt_rand(0,1)?$this->faker->company():null,
            'books' => null,
            'comments' => mt_rand(0,1)?$this->faker->text(500):null,
        ];
    }
}
