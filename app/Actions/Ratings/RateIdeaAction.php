<?php

namespace App\Actions\Ratings;

use App\Actions\Rooms\AssignAuthorToRoomAction;
use App\Models\Idea;
use App\Models\Rating;
use App\Models\User;
use Illuminate\Validation\ValidationException;

class RateIdeaAction
{
    public function __construct(
        private AssignAuthorToRoomAction $assignAuthorAction
    ) {}

    public function execute(Idea $idea, User $user, int $score, ?string $feedback = null): Rating
    {
        // 1. Garante vínculo do usuário com a persona na sala
        $roomUser = $this->assignAuthorAction->execute($idea->room, $user);

        // 2. Impede avaliações duplicadas pelo mesmo usuário
        $alreadyRated = Rating::where('idea_id', $idea->id)
            ->where('user_id', $user->id)
            ->exists();

        if ($alreadyRated) {
            throw ValidationException::withMessages([
                'score' => ['You have already rated this idea.'],
            ]);
        }

        // 3. Registra a avaliação
        $rating = Rating::create([
            'idea_id'   => $idea->id,
            'user_id'   => $user->id,
            'author_id' => $roomUser->author_id,
            'score'     => $score,
            'feedback'  => $feedback,
        ]);

        // 4. Recalcula os agregados na tabela `ideas`
        $newRatingsCount = $idea->ratings()->count();
        $newTotalScore   = (int) $idea->ratings()->sum('score');
        $newAvgScore     = round($newTotalScore / $newRatingsCount, 2);

        $idea->update([
            'total_score'   => $newTotalScore,
            'ratings_count' => $newRatingsCount,
            'avg_score'     => $newAvgScore,
        ]);

        return $rating;
    }
}