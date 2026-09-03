<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Author;
use App\Models\Idea;
use App\Models\Rating;
use App\Models\RoomUser;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RatingController extends Controller
{
    public function store(Request $request, int $ideaId): JsonResponse
    {
        $validated = $request->validate([
            'score'    => 'required|integer|between:1,5',
            'feedback' => 'nullable|string|max:255',
        ]);

        $idea = Idea::findOrFail($ideaId);
        $user = Auth::user();

        // 1. Garante vínculo do usuário com a sala (room_users)
        $roomUser = RoomUser::firstOrCreate(
            [
                'room_id' => $idea->room_id,
                'user_id' => $user->id,
            ],
            [
                'author_id' => $this->assignAvailableAuthor($idea->room_id),
            ]
        );

        // 2. Valida se o usuário já avaliou esta ideia na sala
        $existingRating = Rating::where('idea_id', $idea->id)
            ->where('user_id', $user->id)
            ->exists();

        if ($existingRating) {
            return response()->json([
                'message' => 'You have already rated this idea.',
                'errors'  => ['score' => ['You have already rated this idea.']],
            ], 422);
        }

        // 3. Persiste a avaliação
        $rating = Rating::create([
            'idea_id'   => $idea->id,
            'user_id'   => $user->id,
            'author_id' => $roomUser->author_id,
            'score'     => $validated['score'],
            'feedback'  => $validated['feedback'] ?? null,
        ]);

        // 4. Recalcula os contadores na tabela `ideas`
        $newRatingsCount = $idea->ratings()->count();
        $newTotalScore   = $idea->ratings()->sum('score');
        $newAvgScore     = round($newTotalScore / $newRatingsCount, 2);

        $idea->update([
            'total_score'   => $newTotalScore,
            'ratings_count' => $newRatingsCount,
            'avg_score'     => $newAvgScore,
        ]);

        return response()->json([
            'data' => [
                'id'            => $rating->id,
                'score'         => $rating->score,
                'feedback'      => $rating->feedback,
                'total_score'   => $idea->total_score,
                'ratings_count' => $idea->ratings_count,
                'avg_score'     => number_format($idea->avg_score, 2, '.', ''),
            ],
        ], 201);
    }

    private function assignAvailableAuthor(int $roomId): int
    {
        $usedAuthorIds = RoomUser::where('room_id', $roomId)->pluck('author_id');

        $availableAuthor = Author::where('type', 0)
            ->whereNotIn('id', $usedAuthorIds)
            ->inRandomOrder()
            ->first();

        if (!$availableAuthor) {
            $availableAuthor = Author::where('type', 0)->inRandomOrder()->first();
        }

        return $availableAuthor->id;
    }
}