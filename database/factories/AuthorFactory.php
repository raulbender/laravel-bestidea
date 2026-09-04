<?php

namespace Database\Factories;

use App\Models\Author;
use Illuminate\Database\Eloquent\Factories\Factory;

class AuthorFactory extends Factory
{
    protected $model = Author::class;

    public function definition(): array
    {
        return [
            'name'   => 'author.animal.' . $this->faker->unique()->word(),
            'avatar' => $this->faker->randomElement(['🦁', '🦊', '🐼', '🦉', '🦈', '🐯']),
            'type'   => 0, // Tipo padrão: Humano / Animal
        ];
    }

    /**
     * State para autores do tipo IA / Frutas
     */
    public function ai(): static
    {
        return $this->state(fn (array $attributes) => [
            'name'   => 'author.fruit.' . $this->faker->unique()->word(),
            'avatar' => $this->faker->randomElement(['🥑', '🍋', '🍎', '🍇']),
            'type'   => 1,
        ]);
    }
}