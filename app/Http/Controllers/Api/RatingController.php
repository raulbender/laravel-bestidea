<?php

namespace App\Http\Controllers\Api;

use App\Actions\Ratings\RateIdeaAction;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\RatingResource;
use App\Models\Idea;
use App\Http\Requests\StoreRatingRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class RatingController extends Controller {
    public function store(StoreRatingRequest $request, int $ideaId, RateIdeaAction $rateIdeaAction): JsonResponse {
        $idea = Idea::findOrFail($ideaId);

        $rating = $rateIdeaAction->execute(
            $idea,
            Auth::user(),
            $request->validated(['score']),
            $request->validated(['feedback']) ?? null
        );

        // Recarrega a ideia atualizada para o Resource formatar
        $rating->load('idea');

        return (new RatingResource($rating))
            ->response()
            ->setStatusCode(201);
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request, int $id): AnonymousResourceCollection|JsonResponse {
        $idea = Idea::findOrFail($id);

        $roomUuid = $request->query('room_uuid') ?? $request->input('room_uuid');

        if (!$roomUuid || $idea->room->uuid !== $roomUuid) {
            return response()->json(['message' => 'Unauthorized room access.'], 403);
        }

        $ratings = $idea->ratings()
            ->with('user')
            ->latest()
            ->get();

        return RatingResource::collection($ratings);
    }
}
