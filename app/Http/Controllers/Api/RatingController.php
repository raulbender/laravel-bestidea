<?php

namespace App\Http\Controllers\Api;

use App\Actions\Ratings\RateIdeaAction;
use App\Http\Controllers\Controller;
use App\Models\Idea;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RatingController extends Controller
{
    public function store(Request $request, int $ideaId, RateIdeaAction $rateIdeaAction): JsonResponse
    {
        $validated = $request->validate([
            'score'    => 'required|integer|between:1,5',
            'feedback' => 'nullable|string|max:255',
        ]);

        $idea = Idea::findOrFail($ideaId);

        $rating = $rateIdeaAction->execute(
            $idea,
            Auth::user(),
            $validated['score'],
            $validated['feedback'] ?? null
        );

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
}