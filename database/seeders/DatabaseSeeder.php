<?php

namespace Database\Seeders;

use App\Models\User;
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
        User::factory()->create([ 'email' => 'admin@gsis.com' ])->assignRole('admin');
        User::factory()->create([ 'email' => 'manager@gsis.com' ])->assignRole('manager');
        User::factory()->create([ 'email' => 'user@gsis.com' ])->assignRole('user');
        User::factory(5)->create();
    }
}
