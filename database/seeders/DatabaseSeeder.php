<?php

namespace Database\Seeders;

use Storage;
use App\Models\User;
use App\Models\Order;
use App\Models\Document;
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
        User::factory(10)->create()->each(function ($user) {
            $user->assignRole('user');
        });
        foreach( User::all() as $user ) {
            if( $user->avatar !== "" ) {
                Storage::move(
                    'avatars/'.$user->avatar,
                    'avatars/'.$user->id.'-'.$user->avatar
                );
                $user->update(['avatar' => $user->id.'-'.$user->avatar]);
            }
        }

        // Création des institutions
        Institution::factory(20)->create();

        // Création de commandes
        Order::factory(20)->create();

        Document::factory(10)->create();
        while( count( User::findOrFail(1)->documents ) === 0 )
            Document::factory()->create();
    }
}
