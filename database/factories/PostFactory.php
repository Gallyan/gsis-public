<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\User;
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
        // Object that can be associated with documents
        $object_type = fake()->randomElement([
            Order::class,
        ]);

        // Select object
        if ($object_type === \App\Models\Order::class) {
            // Choisir un élément au hasard ayant un manager, sinon pas de messagerie
            $object = Order::whereIn('status', ['in-progress', 'processed', 'cancelled'])
                ->has('managers')
                ->get()
                ->random(1)
                ->firstOrFail();

            // Existe-t-il déjà des messages liés
            if (count($object->posts) === 0) {
                // Le premier auteur doit être un manager, il existe forcément un manager étant donné le statut de l'objet
                $author = $object->managers->random(1)->first()->user_id;

            } else {
                // Sinon choisir parmi le user et les managers
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
