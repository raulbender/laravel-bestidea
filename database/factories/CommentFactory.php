<?php

namespace Database\Factories;

use App\Models\Author;
use App\Models\Comment;
use App\Models\Idea;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Comment>
 */
class CommentFactory extends Factory
{
    protected $model = Comment::class;

    public function definition(): array
    {
        return [
            'idea_id'   => Idea::factory(),
            'user_id'   => User::factory(),
            'author_id'     => Author::inRandomOrder()->first()?->id ?? Author::create([
                'name'   => $this->faker->name(),
                'avatar' => 'avatar.png',
                'type'   => 0,
            ])->id,
            'content'   => $this->faker->sentence(),
        ];
    }
}