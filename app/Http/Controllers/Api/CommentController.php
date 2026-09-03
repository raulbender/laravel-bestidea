<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Author;
use App\Models\Comment;
use App\Models\Idea;
use App\Models\RoomUser;
use App\Http\Resources\Api\CommentResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class CommentController extends Controller {
    public function store(Request $request, int $id): JsonResponse {
        $validated = $request->validate([
            'content' => 'required|string|max:1000',
        ]);

        $idea = Idea::findOrFail($id);
        $user = Auth::user();

        // 1. Garante vínculo do participante com a sala (persona)
        $roomUser = RoomUser::firstOrCreate(
            [
                'room_id' => $idea->room_id,
                'user_id' => $user->id,
            ],
            [
                'author_id' => $this->assignAvailableAuthor($idea->room_id),
            ]
        );

        // 2. Cria o comentário associando à ideia, usuário e persona
        $comment = Comment::create([
            'idea_id'   => $idea->id,
            'user_id'   => $user->id,
            'author_id' => $roomUser->author_id,
            'content'   => $validated['content'],
        ]);

        // 3. Incrementa o contador na tabela `ideas`
        $idea->increment('comments_count');

        $comment->load('author');
        
        return (new CommentResource($comment))
            ->response()
            ->setStatusCode(201);
    }

    private function assignAvailableAuthor(int $roomId): int {
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

    public function index(int $id): AnonymousResourceCollection {
        $idea = Idea::findOrFail($id);

        $comments = $idea->comments()
            ->with('author')
            ->orderBy('id', 'asc') // Leitura em ordem cronológica
            ->paginate(10);
        
        return CommentResource::collection($comments);
    }
}
