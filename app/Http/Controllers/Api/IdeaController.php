<?php

namespace App\Http\Controllers\Api;

use App\Actions\Ideas\CreateIdeaAction;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\IdeaResource;
use App\Http\Requests\StoreIdeaRequest;
use App\Http\Requests\Api\GetRoomIdeasRequest;
use App\Models\Idea;
use App\Models\Room;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class IdeaController extends Controller 
{
    public function store(StoreIdeaRequest $request, string $uuid, CreateIdeaAction $createIdeaAction): JsonResponse 
    {
        $room = Room::where('uuid', $uuid)->firstOrFail();

        $idea = $createIdeaAction->execute($room, Auth::user(), $request->validated(['content']));

        $idea->load('author');

        return (new IdeaResource($idea))
            ->response()
            ->setStatusCode(201);
    }

    public function index(GetRoomIdeasRequest $request) 
    {
        $room = $request->getRoom();
        $query = $room->ideas()->with('author');

        if ($request->query('filter') === 'mine') {
            $query->where('user_id', $request->user()->id);
        }

        match ($request->query('sort')) {
            'top_rated' => $query->orderBy('avg_score', 'desc')
                ->orderBy('ratings_count', 'desc'),
            'recent'    => $query->orderBy('id', 'desc'),
            'hot'       => $query->orderByRaw('CASE WHEN created_at >= ? THEN 1 ELSE 0 END DESC', [now()->subDays(30)])
                ->orderBy('total_score', 'desc'),
            default     => $query->orderBy('id', 'desc'),
        };

        $ideas = $query->paginate(10);

        return IdeaResource::collection($ideas);
    }
}