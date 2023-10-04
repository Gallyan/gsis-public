<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class UserFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = User::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'lastname' => $this->faker->lastName(),
            'firstname' => $this->faker->firstName(),
            'birthday' => $this->faker->dateTimeBetween('1990-01-01', '2012-12-31')->format('Y-m-d'),
            'phone' => $this->faker->mobileNumber(),
            'email' => $this->faker->unique()->safeEmail(),
            'employer' => $this->faker->company(),
            'email_verified_at' => (bool) random_int(0, 1) ? now() : null,
            'hom_adr' => $this->faker->streetAddress(),
            'hom_zip' => $this->faker->postcode(),
            'hom_cit' => $this->faker->city(),
            'pro_ins' => $this->faker->company(),
            'pro_adr' => $this->faker->streetAddress(),
            'pro_zip' => $this->faker->postcode(),
            'pro_cit' => $this->faker->city(),
            'locale' => (bool) random_int(0, 1) ? 'fr' : 'en',
            'password' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', // password
            'remember_token' => Str::random(10),
            'avatar' => (bool) random_int(0, 1) ? $this->faker->image(storage_path('app/avatars'), 200, 200, null, false) : '',
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function unverified()
    {
        return $this->state(function (array $attributes) {
            return [
                'email_verified_at' => null,
            ];
        });
    }
}
