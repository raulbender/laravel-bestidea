<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Author;
use App\Models\Idea;
use App\Models\Room;
use App\Models\RoomUser;
use App\Http\Resources\Api\IdeaResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class IdeaController extends Controller {
    public function store(Request $request, string $uuid): JsonResponse {
        $validated = $request->validate([
            'content' => 'required|string|max:1000',
        ]);

        $room = Room::where('uuid', $uuid)->firstOrFail();
        $user = Auth::user();

        // Garante que o usuário tem uma persona vinculada nesta sala
        $roomUser = RoomUser::firstOrCreate(
            [
                'room_id' => $room->id,
                'user_id' => $user->id,
            ],
            [
                'author_id' => $this->assignAvailableAuthor($room->id),
            ]
        );

        // Se o registro já existia mas estava sem author_id, atribui um autor disponível
        if (!$roomUser->author_id) {
            $roomUser->update([
                'author_id' => $this->assignAvailableAuthor($room->id),
            ]);
        }

        // Cria a ideia registrando user_id (para banco) e author_id (para anonimato da API)
        $idea = Idea::create([
            'room_id'   => $room->id,
            'user_id'   => $user->id,
            'author_id' => $roomUser->author_id,
            'content'   => $validated['content'],
        ]);

        $idea->load('author');

        return (new IdeaResource($idea))
            ->response()
            ->setStatusCode(201);
    }



    private function assignAvailableAuthor(int $roomId): int {
        $usedAuthorIds = RoomUser::where('room_id', $roomId)
            ->whereNotNull('author_id')
            ->pluck('author_id');

        $availableAuthor = Author::where('type', 0)
            ->whereNotIn('id', $usedAuthorIds)
            ->inRandomOrder()
            ->first();

        if (!$availableAuthor) {
            $availableAuthor = Author::where('type', 0)->inRandomOrder()->first();
        }

        return $availableAuthor->id;
    }

    public function index(Request $request) {
        //$query = Idea::query();
        // Carrega o relacionamento author para satisfazer o Resource
        $query = Idea::with('author');

        // Filtro por sala
        if ($request->filled('room_id')) {
            $query->where('room_id', $request->query('room_id'));
        }

        // Filtro de escopo (Minhas Ideias)
        if ($request->query('filter') === 'mine') {
            $query->where('user_id', $request->user()->id);
        }

        // Ordenações e Rankings
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
