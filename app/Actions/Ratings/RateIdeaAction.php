<?php

namespace App\Actions\Ratings;

use App\Actions\Rooms\AssignAuthorToRoomAction;
use App\Models\Idea;
use App\Models\Rating;
use App\Models\User;

class RateIdeaAction {
    public function __construct(
        private AssignAuthorToRoomAction $assignAuthorAction
    ) {
    }

    public function execute(Idea $idea, User $user, int $score): Rating {
        // 1. Garante o vínculo do usuário com a persona da sala
        $roomUser = $this->assignAuthorAction->execute($idea->room, $user);

        // 2. Cria ou Atualiza a nota existente (Upsert)
        $rating = Rating::updateOrCreate(
            [
                'idea_id' => $idea->id,
                'user_id' => $user->id,
            ],
            [
                'author_id' => $roomUser->author_id,
                'score'     => $score,
            ]
        );

        // 3. Recalcula e atualiza os agregados na tabela `ideas`
        $this->recalculateIdeaStats($idea);

        return $rating;
    }

    public function remove(Idea $idea, User $user): void {
        Rating::where('idea_id', $idea->id)
            ->where('user_id', $user->id)
            ->delete();

        $this->recalculateIdeaStats($idea);
    }

    private function recalculateIdeaStats(Idea $idea): void {
        $newRatingsCount = $idea->ratings()->count();
        $newTotalScore   = (int) $idea->ratings()->sum('score');
        $newAvgScore     = $newRatingsCount > 0 ? round($newTotalScore / $newRatingsCount, 2) : 0;

        $idea->update([
            'total_score'   => $newTotalScore,
            'ratings_count' => $newRatingsCount,
            'avg_score'     => $newAvgScore,
        ]);
    }
}
