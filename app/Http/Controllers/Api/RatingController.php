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

    public function index(Request $request, int $id): JsonResponse {
        $idea = Idea::findOrFail($id);

        // 1. Validação de segurança da Sala (Garante HTTP 403 se o room_uuid for inválido ou ausente)
        $roomUuid = $request->query('room_uuid') ?? $request->input('room_uuid');

        if (!$roomUuid || $idea->room->uuid !== $roomUuid) {
            return response()->json(['message' => 'Unauthorized room access.'], 403);
        }

        // 2. Consulta corrigida usando 'user' ao invés do relacionamento inexistente 'persona'
        $ratings = $idea->ratings()
            ->with('user:id,name')
            ->latest()
            ->get()
            ->map(function ($rating) {
                return [
                    'id'               => $rating->id,
                    'score'            => $rating->score,
                    'author_name'      => $rating->user?->name ?? __('Anônimo'),
                    'created_at_human' => $rating->created_at->diffForHumans(),
                ];
            });

        return response()->json([
            'data' => $ratings,
        ]);
    }
}
