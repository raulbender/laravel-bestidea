<?php

namespace App\Http\Controllers\Api;

use App\Actions\Ratings\RateIdeaAction;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\RatingResource;
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

        // Recarrega a ideia atualizada para o Resource formatar
        $rating->load('idea');

        return (new RatingResource($rating))
            ->response()
            ->setStatusCode(201);
    }
}