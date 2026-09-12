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

class RoomFactory extends Factory {
    protected $model = Room::class;

    public function definition(): array {
        return [
            'uuid'        => (string) Str::uuid(),
            'user_id'     => User::factory(),
            'description' => $this->faker->sentence(),
            'is_public'   => true,
            'expires_at'  => fake()->boolean(50) ? now()->addHours(fake()->numberBetween(1, 360)) : null,
        ];
    }

    /**
     * State para salas prestes a expirar ou com tempo específico em dias
     */
    public function expiresInDays(int $days): static {
        return $this->state(fn(array $attributes) => [
            'expires_at' => now()->addDays($days),
        ]);
    }

    /**
     * State para salas sem expiração
     */
    public function neverExpires(): static {
        return $this->state(fn(array $attributes) => [
            'expires_at' => null,
        ]);
    }

    /**
     * State para garantir que a sala seja criada como PÚBLICA
     */
    public function public(): static {
        return $this->state(fn(array $attributes) => [
            'is_public' => true,
        ]);
    }

    /**
     * State para garantir que a sala seja criada como PRIVADA
     */
    public function private(): static {
        return $this->state(fn(array $attributes) => [
            'is_public' => false,
        ]);
    }

    public function withFullContent(?int $ideasCount = null): static {
        return $this->afterCreating(function (Room $room) use ($ideasCount) {
            // Se não for informado um valor fixo, sorteia entre 3 e 12 ideias
            $count = $ideasCount ?? fake()->numberBetween(3, 12);

            $users = User::factory(fake()->numberBetween(5, 15))->create();
            $authors = Author::all();

            $ensureRoomUser = function (int $roomId, string $userId) use ($authors) {
                return RoomUser::firstOrCreate(
                    ['room_id' => $roomId, 'user_id' => $userId],
                    ['author_id' => $authors->random()->id]
                );
            };

            $ensureRoomUser($room->id, $room->user_id);

            Idea::factory($count)
                ->for($room)
                ->recycle($users)
                ->state(fn() => [
                    'created_at' => now()->subMinutes(fake()->numberBetween(10, 21600)),
                ])
                ->create()
                ->each(function (Idea $idea) use ($room, $users, $ensureRoomUser) {

                    $ensureRoomUser($room->id, $idea->user_id);

                    // Helper para gerar datas coerentes (após a ideia e antes de agora)
                $getRandomCommentDate = fn () => fake()->dateTimeBetween($idea->created_at, 'now');

                    // Criar comentários (1 a 5 por ideia)
                    $comments = Comment::factory(fake()->numberBetween(1, 5))
                        ->for($idea)
                        ->recycle($users)
                        ->state(fn () => [
                        'created_at' => $getRandomCommentDate(),
                    ])
                        ->create();

                    foreach ($comments as $comment) {
                        $ensureRoomUser($room->id, $comment->user_id);
                    }

                    // Criar avaliações (1 até total de usuários)
                    $ratingsCount = fake()->numberBetween(1, min(6, $users->count()));
                    $randomUsersForRatings = $users->shuffle()->take($ratingsCount);

                    $totalScore = 0;
                    $extraCommentsCount = 0;
                    foreach ($randomUsersForRatings as $user) {
                        $ratingDate = $getRandomCommentDate();

                        $rating = Rating::factory()->create([
                            'idea_id' => $idea->id,
                            'user_id' => $user->id,
                            'created_at' => $ratingDate,
                        ]);

                        // Em 50% das avaliações, gera também um comentário atrelado ao mesmo usuário
                        if (fake()->boolean(50)) {
                            Comment::factory()->create([
                                'idea_id' => $idea->id,
                                'user_id' => $user->id,
                                'rating_id' => $rating->id,
                                'created_at' => $ratingDate, // Comentário da avaliação com a mesma data da nota
                            ]);
                            $extraCommentsCount++;
                        }

                        $totalScore += $rating->score;
                        $ensureRoomUser($room->id, $user->id);
                    }

                    // Atualiza os contadores reais na model de Ideia
                    $realRatingsCount = $randomUsersForRatings->count();
                    $totalCommentsCount = $comments->count() + $extraCommentsCount;

                    $idea->update([
                        'comments_count' => $totalCommentsCount,
                        'ratings_count'  => $realRatingsCount,
                        'total_score'    => $totalScore,
                        'avg_score'      => $realRatingsCount > 0 ? round($totalScore / $realRatingsCount, 2) : 0,
                    ]);
                });
        });
    }
}
