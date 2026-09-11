<?php

namespace Database\Factories;

use App\Models\Author;
use App\Models\Idea;
use App\Models\Rating;
use App\Models\User;
use App\Models\Comment;
use Illuminate\Database\Eloquent\Factories\Factory;

class RatingFactory extends Factory
{
    protected $model = Rating::class;

    public function definition(): array
    {
        return [
            'idea_id'   => Idea::factory(),
            'user_id'   => User::factory(),
            'author_id' => Author::inRandomOrder()->first()?->id,
            'score'     => $this->faker->numberBetween(1, 5),            
        ];
    }

    /**
     * Indica que este rating deve acompanhar um comentário na ideia.
     */
    public function withComment(?string $body = null): static
    {
        return $this->afterCreating(function (Rating $rating) use ($body) {
            Comment::factory()->create([
                'idea_id' => $rating->idea_id,
                'user_id' => $rating->user_id,
                'rating_id' => $rating->id,
                'body'    => $body ?? $this->faker->paragraph(),
            ]);
        });
    }
}