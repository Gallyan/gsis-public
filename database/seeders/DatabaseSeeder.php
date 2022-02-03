<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Order;
use App\Models\Institution;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;


class DatabaseSeeder extends Seeder
{
    public function run()
    {
        // Creation des roles
		$admin = Role::create(['name' => 'admin']);
		$manager = Role::create(['name' => 'manager']);
		$user = Role::create(['name' => 'user']);

        // Creation des permissions
        Permission::create(['name' => 'manage-users']);

        // Assign permissions to roles
        $manager->givePermissionTo('manage-users');
        $admin->givePermissionTo('manage-users');

        // Création des utilisateurs
        User::factory()->create([ 'email' => 'admin@gsis.com', 'email_verified_at' => now() ])->assignRole('admin')->assignRole('manager');
        User::factory()->create([ 'email' => 'manager@gsis.com' ])->assignRole('manager');
        User::factory()->create([ 'email' => 'user@gsis.com' ])->assignRole('user');
        User::factory(10)->create();
        foreach( User::all() as $user ) {
            $user->assignRole('user');
        }

        // Création des institutions
        Institution::factory(20)->create();

        // Création de commandes
        Order::factory(20)->create();
    }
}
