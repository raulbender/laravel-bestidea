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
            $request->validated(['score'])
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

    public function destroy(Request $request, int $ideaId, RateIdeaAction $rateIdeaAction): JsonResponse {
        $idea = Idea::findOrFail($ideaId);
        $roomUuid = $request->query('room_uuid') ?? $request->input('room_uuid');

        if (!$roomUuid || $idea->room->uuid !== $roomUuid) {
            return response()->json(['message' => 'Unauthorized room access.'], 403);
        }

        $rateIdeaAction->remove($idea, Auth::user());

        return response()->json([
            'message' => 'Rating removed successfully.'
        ], 200);
    }

    public function myRating(Request $request, int $id): JsonResponse {
        $idea = Idea::findOrFail($id);

        $roomUuid = $request->query('room_uuid') ?? $request->input('room_uuid');

        if (!$roomUuid || $idea->room->uuid !== $roomUuid) {
            return response()->json(['message' => 'Unauthorized room access.'], 403);
        }

        $user = Auth::user();

        $rating = $idea->ratings()
            ->where('user_id', $user->id)
            ->first();

        if (!$rating) {
            return response()->json([
                'score'   => null,
                'comment' => null,
            ]);
        }

        $comment = \App\Models\Comment::where('rating_id', $rating->id)
            ->where('user_id', $user->id)
            ->first();

        return response()->json([
            'id'      => $rating->id,
            'score'   => $rating->score,
            'comment' => $comment ? [
                'id'      => $comment->id,
                'content' => $comment->content,
            ] : null,
        ]);
    }
}
