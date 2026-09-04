<?php

namespace App\Http\Controllers\Api;

use App\Actions\Ideas\CreateIdeaAction;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\IdeaResource;
use App\Models\Idea;
use App\Models\Room;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class IdeaController extends Controller 
{
    public function store(Request $request, string $uuid, CreateIdeaAction $createIdeaAction): JsonResponse 
    {
        $validated = $request->validate([
            'content' => 'required|string|max:1000',
        ]);

        $room = Room::where('uuid', $uuid)->firstOrFail();

        $idea = $createIdeaAction->execute($room, Auth::user(), $validated['content']);

        $idea->load('author');

        return (new IdeaResource($idea))
            ->response()
            ->setStatusCode(201);
    }

    public function index(Request $request) 
    {
        $query = Idea::with('author');

        if ($request->filled('room_id')) {
            $query->where('room_id', $request->query('room_id'));
        }

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