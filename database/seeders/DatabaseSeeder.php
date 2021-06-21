<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        User::factory()->create([
            'email' => 'admin@gsis.com',
        ]);

        User::factory()->create([
            'email' => 'manager@gsis.com',
        ]);

        User::factory()->create([
            'email' => 'user@gsis.com',
        ]);

        User::factory(5)->create();

        $this->call(RolesTableSeeder::class);
    }
}
