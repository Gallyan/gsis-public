<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        $this->call(RolesTableSeeder::class);

        User::factory()->create([
            'email' => 'dev@gsis.com',
        ]);

        User::factory(5)->create();
    }
}
