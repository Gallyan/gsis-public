<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;

class RolesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Creation des roles
		Role::create(['name' => 'admin']);
		Role::create(['name' => 'manager']);
		Role::create(['name' => 'user']);

        // Creation des permissions
        Permission::create(['name' => 'manage-users']);

        // Assign permissions to roles
        $manager = Role::findByName('manager');
        $manager->givePermissionTo('manage-users');

        $admin = Role::findByName('admin');
        $admin->givePermissionTo('manage-users');

        // Assign roles to users
        $user = User::find(1);
        $user->assignRole('admin');

        $user = User::find(2);
        $user->assignRole('manager');

        $user = User::find(3);
        $user->assignRole('user');
    }
}
