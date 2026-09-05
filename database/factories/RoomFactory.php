<?php

namespace Database\Factories;

use App\Models\Author;
use App\Models\Comment;
use App\Models\Idea;
use App\Models\Rating;
use App\Models\Room;
use App\Models\RoomUser;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class RoomFactory extends Factory 
{
    protected $model = Room::class;

    public function definition(): array 
    {
        return [
            'uuid'        => (string) Str::uuid(),
            'user_id'     => User::factory(),
            'description' => $this->faker->sentence(),
            'is_public'   => $this->faker->boolean(),
            'expires_at'  => now()->addDays(7),
        ];
    }

    public function withFullContent(int $ideasCount = 5): static 
    {
        return $this->afterCreating(function (Room $room) use ($ideasCount) {
            $users = User::factory(10)->create();

            $authors = Author::all();
            
            // Função helper para vincular o usuário à sala garantindo um author_id
            $ensureRoomUser = function (int $roomId, string $userId) use ($authors) {
                return RoomUser::firstOrCreate(
                    [
                        'room_id' => $roomId,
                        'user_id' => $userId,
                    ],
                    [
                        'author_id' => $authors->random()->id,
                    ]
                );
            };

            // 1. Vincula o criador da sala
            $ensureRoomUser($room->id, $room->user_id);

            Idea::factory($ideasCount)
                ->for($room)
                ->recycle($users)
                ->create([
                    'avg_score'      => round(random_int(0, 500) / 100, 2),
                    'comments_count' => random_int(5, 100),
                    'total_score'    => random_int(100, 300),
                    'ratings_count'  => random_int(5, 100),
                ])
                ->each(function (Idea $idea) use ($room, $users, $ensureRoomUser) {

                    // 2. Vincula o autor da ideia na sala
                    $ensureRoomUser($room->id, $idea->user_id);

                    // Comentários
                    $comments = Comment::factory(rand(1, 3))
                        ->for($idea)
                        ->recycle($users)
                        ->create();

                    foreach ($comments as $comment) {
                        $ensureRoomUser($room->id, $comment->user_id);
                    }

                    // Avaliações
                    $ratingsCount = rand(1, min(5, $users->count()));
                    $randomUsersForRatings = $users->shuffle()->take($ratingsCount);

                    foreach ($randomUsersForRatings as $user) {
                        Rating::factory()->create([
                            'idea_id' => $idea->id,
                            'user_id' => $user->id,
                        ]);

                        $ensureRoomUser($room->id, $user->id);
                    }
                });
        });
    }
}