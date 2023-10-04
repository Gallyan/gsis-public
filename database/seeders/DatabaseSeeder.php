<?php

namespace Database\Seeders;

use App\Models\Document;
use App\Models\Institution;
use App\Models\Manager;
use App\Models\Order;
use App\Models\Post;
use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Storage;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Creation des roles
        foreach (['admin', 'manager', 'user'] as $role) {
            Role::findOrCreate($role);
        }

        // Creation des permissions
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

        // Création des utilisateurs type
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

        // Création d'utilisateurs lambda
        User::factory(10)->create()->each(function ($user) {
            $user->assignRole('user');
        });

        // Mise à jour du nom de l'avatar avec l'id utilisateur que l'on ne connait pas à la création de l'utilisateur
        foreach (User::all() as $user) {
            if ($user->avatar !== '' && ! preg_match('/^'.$user->id.'\-/', $user->avatar)) {
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

        // Création de documents pour les commandes ou les utilisateurs
        Document::factory(10)->create();

        // Création d'au moins un document pour l'admin
        while (count(User::findOrFail(1)->documents) === 0) {
            Document::factory()->create();
        }

        // Affectation des managers à des commandes et autres éléments
        Manager::factory(10)->create();

        // Création de messages
        Post::factory(100)->create();
    }
}
