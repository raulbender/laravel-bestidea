<?php

namespace Database\Factories;

use App\Models\Room;
use App\Models\User;
use App\Models\Idea;
use App\Models\Comment;
use App\Models\Rating;
use Illuminate\Database\Eloquent\Factories\Factory;

class RoomFactory extends Factory {
    protected $model = Room::class;

    public function definition(): array {
        return [
            'user_id'     => User::factory(),
            'description' => $this->faker->sentence(),
            'is_public'   => $this->faker->boolean(),
            'expires_at'  => now()->addDays(7),
        ];
    }

    /**
     * Estado para gerar uma sala completa com ideias, comentários e ratings de uma só vez.
     */
    public function withFullContent(int $ideasCount = 5): static {
        return $this->afterCreating(function (Room $room) use ($ideasCount) {
            // Criamos uma quantidade de usuários suficiente para os testes
            $users = User::factory(10)->create();

            Idea::factory($ideasCount)
                ->for($room)
                ->recycle($users)
                ->create()
                ->each(function (Idea $idea) use ($users) {

                    // Comentários (podem repetir o mesmo usuário)
                    Comment::factory(rand(1, 3))
                        ->for($idea)
                        ->recycle($users)
                        ->create();

                    // Avaliações: garantimos usuários ÚNICOS por ideia usando shuffle/take
                    $ratingsCount = rand(1, min(5, $users->count()));
                    $randomUsersForRatings = $users->shuffle()->take($ratingsCount);

                    foreach ($randomUsersForRatings as $user) {
                        Rating::factory()->create([
                            'idea_id' => $idea->id,
                            'user_id' => $user->id,
                        ]);
                    }
                });
        });
    }
}
