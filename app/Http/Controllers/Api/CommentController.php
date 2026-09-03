<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Author;
use App\Models\Comment;
use App\Models\Idea;
use App\Models\RoomUser;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CommentController extends Controller
{
    public function store(Request $request, int $id): JsonResponse
    {
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

        return response()->json([
            'data' => [
                'id'         => $comment->id,
                'content'    => $comment->content,
                'author'     => [
                    'name'   => $comment->author->name,
                    'avatar' => $comment->author->avatar,
                    'type'   => $comment->author->type,
                ],
                'created_at' => $comment->created_at,
            ],
        ], 201);
    }

    private function assignAvailableAuthor(int $roomId): int
    {
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
}