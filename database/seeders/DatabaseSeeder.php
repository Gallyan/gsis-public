<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        User::factory()->create([
            'email' => 'dev@gsis.com',
        ]);

        User::factory(5)->create();

        $this->call(RolesTableSeeder::class);
    }
}
