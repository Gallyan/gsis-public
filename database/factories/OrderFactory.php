<?php

namespace Database\Factories;

use App\Models\Institution;
use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class OrderFactory extends Factory
{
    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        $institutions = Institution::all()->pluck('id');

        $users = User::all()->pluck('id');

        $status = array_keys(Order::STATUSES);
        $editions = array_keys(Order::EDITION);

        $books = [];
        if (mt_rand(0, 1)) {
            for ($i = 0; $i < mt_rand(1, 5); $i++) {
                $books[$i]['title'] = fake()->sentence(mt_rand(3, 5));
                $books[$i]['author'] = fake()->name();
                $books[$i]['isbn'] = fake()->isbn13();
                $books[$i]['edition'] = $editions[mt_rand(0, count($editions) - 1)];
            }
        }

        return [
            'user_id' => $users[mt_rand(0, count($users) - 1)],
            'subject' => fake()->sentence(),
            'institution_id' => $institutions[mt_rand(0, count($institutions) - 1)],
            'supplier' => mt_rand(0, 1) ? fake()->company() : null,
            'books' => $books,
            'comments' => mt_rand(0, 1) ? fake()->text(500) : null,
            'status' => $status[mt_rand(0, count($status) - 1)],
            'amount' => mt_rand(0, 1) ? (float) (random_int(100, 1000000) / 100) : null,
        ];
    }
}
