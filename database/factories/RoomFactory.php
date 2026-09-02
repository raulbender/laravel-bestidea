<?php

namespace Database\Factories;

use App\Models\Room;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class RoomFactory extends Factory
{
    protected $model = Room::class;

    public function definition(): array
    {
        return [
            'user_id'     => User::factory(),
            'description' => $this->faker->sentence(),
            'is_public'   => $this->faker->boolean(),
            'expires_at'  => now()->addDays(7),
        ];
    }
}