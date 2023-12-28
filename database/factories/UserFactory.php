<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class UserFactory extends Factory
{
    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        if ( (bool) random_int(0, 1) ) {
            $last_login_ip = fake()->ipv4();
            $last_login_at = fake()->dateTimeBetween('-1 years', 'now');
            $last_seen_at = fake()->dateTimeBetween($last_login_at, 'now');
        } else {
            $last_login_ip = null;
            $last_login_at = null;
            $last_seen_at = null;
        }

        return [
            'lastname' => fake()->lastName(),
            'firstname' => fake()->firstName(),
            'birthday' => fake()->dateTimeBetween('1990-01-01', '2012-12-31')->format('Y-m-d'),
            'phone' => fake()->mobileNumber(),
            'email' => fake()->unique()->safeEmail(),
            'employer' => fake()->company(),
            'email_verified_at' => (bool) random_int(0, 1) ? fake()->datetime() : null,
            'hom_adr' => fake()->streetAddress(),
            'hom_zip' => fake()->postcode(),
            'hom_cit' => fake()->city(),
            'pro_ins' => fake()->company(),
            'pro_adr' => fake()->streetAddress(),
            'pro_zip' => fake()->postcode(),
            'pro_cit' => fake()->city(),
            'locale' => (bool) random_int(0, 1) ? 'fr' : 'en',
            'password' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', // password
            'remember_token' => Str::random(10),
            'avatar' => (bool) random_int(0, 1) ? fake()->image(storage_path('app/avatars'), 200, 200, null, false) : '',
            'last_login_ip' => $last_login_ip,
            'last_login_at' => $last_login_at,
            'last_seen_at' => $last_seen_at,
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): Factory
    {
        return $this->state(function (array $attributes) {
            return [
                'email_verified_at' => null,
            ];
        });
    }
}
