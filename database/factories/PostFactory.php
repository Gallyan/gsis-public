<?php

namespace Database\Factories;

use App\Models\Order;
//use App\Models\Purchase;
//use App\Models\Mission;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Post>
 */
class PostFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        // Object that can be associated with posts
        $object_type = fake()->randomElement([
            Order::class,
            //Mission::class,
            //Purchase::class,
        ]);

        // Select random object that has a manager, otherwise no message
        $object = $object_type::whereIn('status', ['in-progress', 'processed', 'cancelled'])
            ->withCount('posts')
            ->with('managers')
            ->has('managers')
            ->inRandomOrder()
            ->firstOrFail();

        // First post must be a manager post
        if ($object->posts_count === 0) {
            $author = $object->managers->pluck('user_id')->random();

        } else {
            // Otherwise choose between user and managers
            $author = array_rand(
                array_flip(
                    array_unique(
                        array_merge(
                            $object->managers->pluck('user_id')->toArray(),
                            [$object->user_id]
                        )
                    )
                )
            );
        }

        return [
            'user_id' => $author,
            'postable_id' => $object->id,
            'postable_type' => $object_type,
            'body' => fake()->paragraphs(mt_rand(1, 3), true),
            'read_at' => ($author === $object->user_id || mt_rand(0, 1)) ? now() : null,
        ];
    }
}
