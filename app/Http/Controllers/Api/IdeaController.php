<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Author;
use App\Models\Idea;
use App\Models\Room;
use App\Models\RoomUser;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class IdeaController extends Controller
{
    public function store(Request $request, string $uuid): JsonResponse
    {
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

        // Cria a ideia registrando user_id (para banco) e author_id (para anonimato da API)
        $idea = Idea::create([
            'room_id'   => $room->id,
            'user_id'   => $user->id,
            'author_id' => $roomUser->author_id,
            'content'   => $validated['content'],
        ]);

        $idea->load('author');

        return response()->json([
            'data' => [
                'id'         => $idea->id,
                'content'    => $idea->content,
                'author'     => [
                    'name'   => $idea->author->name,
                    'avatar' => $idea->author->avatar,
                    'type'   => $idea->author->type,
                ],
                'created_at' => $idea->created_at,
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