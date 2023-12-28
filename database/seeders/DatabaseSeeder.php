<?php

namespace Database\Seeders;

use App\Models\Document;
use App\Models\Institution;
use App\Models\Manager;
use App\Models\Order;
use App\Models\Post;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Roles creation
        foreach (['admin', 'manager', 'user'] as $role) {
            Role::findOrCreate($role);
        }

        // Permissions creation
        foreach (['manage-users', 'manage-roles', 'manage-admin'] as $permission) {
            Permission::findOrCreate($permission);
        }

        // Assign permissions to roles
        Role::findByName('manager')
            ->givePermissionTo('manage-users')
            ->givePermissionTo('manage-roles');

        Role::findByName('admin')
            ->givePermissionTo('manage-users')
            ->givePermissionTo('manage-roles')
            ->givePermissionTo('manage-admin');

        // Create users for each roles
        if (! User::where('email', 'admin@gsis.com')->first()) {
            User::factory()
                ->create(['email' => 'admin@gsis.com', 'email_verified_at' => now()])
                ->assignRole('admin')
                ->assignRole('manager');
        }

        if (! User::where('email', 'manager@gsis.com')->first()) {
            User::factory()
                ->create(['email' => 'manager@gsis.com', 'email_verified_at' => now()])
                ->assignRole('manager');
        }

        if (! User::where('email', 'user@gsis.com')->first()) {
            User::factory()
                ->create(['email' => 'user@gsis.com', 'email_verified_at' => now()])
                ->assignRole('user');
        }

        // Create users
        User::factory(10)->create()->each(function ($user) {
            $user->assignRole('user');
        });

        // Update avatar name by adding user id (unkown at user creation)
        foreach (User::all() as $user) {
            if ($user->avatar !== '' && ! preg_match('/^'.$user->id.'\-/', $user->avatar)) {
                Storage::move(
                    'avatars/'.$user->avatar,
                    'avatars/'.$user->id.'-'.$user->avatar
                );
                $user->update(['avatar' => $user->id.'-'.$user->avatar]);
            }
        }

        Log::info('Seeding Institution');
        Institution::factory(20)->create();

        Log::info('Seeding Order');
        Order::factory(20)->create();

        Log::info('Seeding Document');
        Document::factory(10)->create();

        Log::info('Seeding User-Document');
        while (count(User::findOrFail(1)->documents) === 0) {
            Document::factory()->create();
        }

        Log::info('Seeding Manager');
        Manager::factory(10)->create();

        Log::info('Seeding Post');
        Post::factory(100)->create();
    }
}
