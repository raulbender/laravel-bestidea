<?php

namespace Database\Factories;

use App\Models\Author;
use App\Models\Idea;
use App\Models\Rating;
use App\Models\User;
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
            'feedback'  => $this->faker->sentence(),
        ];
    }
}