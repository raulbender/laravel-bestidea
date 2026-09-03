<?php

namespace Database\Factories;

use App\Models\Author;
use App\Models\Room;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Idea>
 */
class IdeaFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id'       => User::factory(),
            // Pega um autor existente do AuthorSeeder em vez de tentar chamar Author::factory()
            'author_id'     => Author::inRandomOrder()->first()?->id ?? Author::create([
                'name'   => $this->faker->name(),
                'avatar' => 'avatar.png',
                'type'   => 0,
            ])->id,
            'room_id'       => Room::factory(),
            'content'       => $this->faker->sentence(),
            'total_score'   => 0,
            'ratings_count' => 0,
            'avg_score'     => 0.00,
        ];
    }
}