<?php

namespace Database\Seeders;

use App\Models\Author;
use Illuminate\Database\Seeder;

class AuthorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $authors = [
            // IA - Frutas (Type 1)
            ['name' => 'author.fruit.avocado', 'avatar' => '🥑', 'type' => 1],
            ['name' => 'author.fruit.lemon',   'avatar' => '🍋', 'type' => 1],
            ['name' => 'author.fruit.apple',   'avatar' => '🍎', 'type' => 1],
            ['name' => 'author.fruit.grape',   'avatar' => '🍇', 'type' => 1],

            // Humanos - Animais (Type 0)
            ['name' => 'author.animal.lion',      'avatar' => '🦁', 'type' => 0],
            ['name' => 'author.animal.fox',       'avatar' => '🦊', 'type' => 0],
            ['name' => 'author.animal.panda',     'avatar' => '🐼', 'type' => 0],
            ['name' => 'author.animal.owl',       'avatar' => '🦉', 'type' => 0],
            ['name' => 'author.animal.shark',     'avatar' => '🦈', 'type' => 0],
            ['name' => 'author.animal.tiger',     'avatar' => '🐯', 'type' => 0],
            ['name' => 'author.animal.bear',      'avatar' => '🐻', 'type' => 0],
            ['name' => 'author.animal.koala',     'avatar' => '🐨', 'type' => 0],
            ['name' => 'author.animal.rabbit',    'avatar' => '🐰', 'type' => 0],
            ['name' => 'author.animal.wolf',      'avatar' => '🐺', 'type' => 0],
            ['name' => 'author.animal.frog',      'avatar' => '🐸', 'type' => 0],
            ['name' => 'author.animal.monkey',    'avatar' => '🐵', 'type' => 0],
            ['name' => 'author.animal.pig',       'avatar' => '🐷', 'type' => 0],
            ['name' => 'author.animal.dog',       'avatar' => '🐶', 'type' => 0],
            ['name' => 'author.animal.cat',       'avatar' => '🐱', 'type' => 0],
            ['name' => 'author.animal.mouse',     'avatar' => '🐭', 'type' => 0],
            ['name' => 'author.animal.hamster',   'avatar' => '🐹', 'type' => 0],
            ['name' => 'author.animal.dragon',    'avatar' => '🐲', 'type' => 0],
            ['name' => 'author.animal.whale',     'avatar' => '🐳', 'type' => 0],
            ['name' => 'author.animal.octopus',   'avatar' => '🐙', 'type' => 0],
            ['name' => 'author.animal.crab',      'avatar' => '🦀', 'type' => 0],
            ['name' => 'author.animal.bee',       'avatar' => '🐝', 'type' => 0],
            ['name' => 'author.animal.butterfly', 'avatar' => '🦋', 'type' => 0],
            ['name' => 'author.animal.turtle',    'avatar' => '🐢', 'type' => 0],
            ['name' => 'author.animal.snake',     'avatar' => '🐍', 'type' => 0],
            ['name' => 'author.animal.horse',     'avatar' => '🐴', 'type' => 0],
            ['name' => 'author.animal.sheep',     'avatar' => '🐑', 'type' => 0],
            ['name' => 'author.animal.elephant',  'avatar' => '🐘', 'type' => 0],
            ['name' => 'author.animal.giraffe',   'avatar' => '🦒', 'type' => 0],
            ['name' => 'author.animal.penguin',   'avatar' => '🐧', 'type' => 0],
            ['name' => 'author.animal.duck',      'avatar' => '🦆', 'type' => 0],
            ['name' => 'author.animal.bat',       'avatar' => '🦇', 'type' => 0],
            ['name' => 'author.animal.eagle',     'avatar' => '🦅', 'type' => 0],
            ['name' => 'author.animal.boar',      'avatar' => '🐗', 'type' => 0],
        ];

        foreach ($authors as $author) {
            Author::firstOrCreate(
                ['name' => $author['name']],
                $author
            );
        }
    }
}