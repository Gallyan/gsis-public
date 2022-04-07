<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\Order;
use App\Models\Document;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Document>
 */
class DocumentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        // Select owner
        $users = User::all()->pluck('id');
        $owner_id = $users[mt_rand(0,count($users)-1)];

        // Create user documents directory if not exists
        $path = 'docs/'.$owner_id.'/';
        Storage::makeDirectory( $path );

        // Create fake file
        $filename = $this->faker->image( storage_path('app/'.$path), mt_rand(100,400), mt_rand(50,200), null, false );

        // Object that can be associated with documents
        $documentable = $this->faker->randomElement([
            User::class,
            Order::class,
        ]);

        return [
            'name' => $this->faker->sentence(),
            'type' => $documentable === User::class ?
                    $this->faker->randomElement(['driver' ,'bank', 'passport', 'id']) :
                    $this->faker->randomElement(['quotation']),
            'size' => Storage::size( $path.$filename ),
            'filename' => $filename,
            'user_id' => $owner_id,
            'documentable_id' => $documentable === User::class ? $owner_id : $documentable::factory(),
            'documentable_type' => $documentable,
        ];
    }
}