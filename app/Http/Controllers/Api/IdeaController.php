<?php

namespace App\Http\Controllers\Api;

use App\Actions\Ideas\CreateIdeaAction;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\IdeaResource;
use App\Http\Requests\StoreIdeaRequest;
use App\Http\Requests\Api\GetRoomIdeasRequest;
use App\Models\Idea;
use App\Models\Room;
use App\Models\Rating; // <-- Não esqueça de importar o model
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class IdeaController extends Controller {
    public function store(StoreIdeaRequest $request, string $uuid, CreateIdeaAction $createIdeaAction): JsonResponse {
        $room = Room::where('uuid', $uuid)->firstOrFail();

        $idea = $createIdeaAction->execute($room, Auth::user(), $request->validated(['content']));

        $idea->load('author');

        return (new IdeaResource($idea))
            ->response()
            ->setStatusCode(201);
    }

    public function index(GetRoomIdeasRequest $request) {
        $room = $request->getRoom();
        $user = $request->user();

        // 1. O select('ideas.*') garante que os dados base venham antes do sub-select
        $query = $room->ideas()->select('ideas.*')->with('author');

        // 2. Sub-select: Busca a nota apenas deste usuário para cada ideia (Performance Otimizada)
        if ($user) {
            $query->addSelect([
                'my_rating' => Rating::select('score')
                    ->whereColumn('idea_id', 'ideas.id')
                    ->where('user_id', $user->id)
                    ->limit(1)
            ]);
        }

        if ($request->query('filter') === 'mine' && $user) {
            $query->where('user_id', $user->id);
        }

        match ($request->query('sort')) {
            'top_rated' => $query->orderBy('avg_score', 'desc')->orderBy('ratings_count', 'desc'),
            'recent'    => $query->orderBy('id', 'desc'),
            'hot'       => $query->orderByRaw('CASE WHEN created_at >= ? THEN 1 ELSE 0 END DESC', [now()->subDays(30)])
                ->orderBy('total_score', 'desc'),
            default     => $query->orderBy('id', 'desc'),
        };

        return IdeaResource::collection($query->paginate(10));
    }
}
