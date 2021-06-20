<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

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
    }
}
