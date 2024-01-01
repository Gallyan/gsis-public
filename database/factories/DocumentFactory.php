<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Storage;

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
    public function definition(): array
    {
        // Object that can be associated with documents
        $documentable = fake()->randomElement([
            \App\Models\User::class,
            \App\Models\Order::class,
            \App\Models\Purchase::class,
            \App\Models\Reception::class,
        ]);
        $documentable_id = fake()->randomElement($documentable::pluck('id'));

        // Select owner
        if($documentable === 'App\Models\User') {
            $type = fake()->randomElement(array_keys(\App\Models\User::DOCTYPE));
            $owner_id = $documentable_id;
        }elseif($documentable === 'App\Models\Reception') {
            $type = 'guestlist';
            $owner_id = $documentable::find($documentable_id)->purchase->user_id;
        }else{
            $owner_id = $documentable::find($documentable_id)->user_id;
            if($documentable === 'App\Models\Order') {
                $type = 'quotation';
            }else{
                $type = 'document';
            }
        }

        // Create user documents directory if not exists
        $path = 'docs/'.$owner_id.'/';
        Storage::makeDirectory($path);

        // Create fake file
        $filename = fake()->image(storage_path('app/'.$path), mt_rand(100, 600), mt_rand(50, 400), null, false);

        return [
            'name' => fake()->sentence(),
            'type' => $type,
            'size' => Storage::size($path.$filename),
            'filename' => $filename,
            'user_id' => $owner_id,
            'documentable_id' => $documentable_id,
            'documentable_type' => $documentable,
        ];
    }
}
