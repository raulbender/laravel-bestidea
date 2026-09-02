<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Room;
use App\Models\RoomUser;
use App\Models\Author;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RoomController extends Controller {
    /**
     * Store a newly created room in storage.
     */
    public function store(Request $request): JsonResponse {
        $validated = $request->validate([
            'description' => 'required|string|max:255',
            'is_public'   => 'nullable|boolean',
            'expires_at'  => 'nullable|date|after:now',
        ]);

        $room = Room::create([...$validated, 'user_id' => Auth::id()]);

        return response()->json([
            'data' => $room,
        ], 201);
    }

    // /**
    //  * Fetch a single room by its UUID.
    //  */
    // public function show(string $uuid): JsonResponse
    // {
    //     $room = Room::where('uuid', $uuid)->firstOrFail();

    //     return response()->json([
    //         'data' => $room,
    //     ], 200);
    // }

    public function show(string $uuid): JsonResponse {
        $room = Room::where('uuid', $uuid)->firstOrFail();
        $user = Auth::user();

        // 1. Garante ou cria a associação do usuário com um Author anônimo nesta sala
        $roomUser = RoomUser::firstOrCreate(
            [
                'room_id' => $room->id,
                'user_id' => $user->id,
            ],
            [
                'author_id' => $this->assignAvailableAuthor($room->id),
            ]
        );

        // 2. Carrega os dados do autor sorteado
        $roomUser->load('author');

        return response()->json([
            'data' => [
                'room' => $room,
                'my_persona' => [
                    'name'   => $roomUser->author->name,
                    'avatar' => $roomUser->author->avatar,
                    'type'   => $roomUser->author->type,
                ],
                'is_owner' => $room->user_id === $user->id,
            ],
        ], 200);
    }

    private function assignAvailableAuthor(int $roomId): int {
        // IDs de autores (animais) já em uso nesta sala
        $usedAuthorIds = RoomUser::where('room_id', $roomId)->pluck('author_id');

        // Busca um animal (type = 0) ainda disponível
        $availableAuthor = Author::where('type', 0)
            ->whereNotIn('id', $usedAuthorIds)
            ->inRandomOrder()
            ->first();

        // Se todos os animais já estiverem ocupados, seleciona qualquer animal aleatório
        if (!$availableAuthor) {
            $availableAuthor = Author::where('type', 0)->inRandomOrder()->first();
        }

        return $availableAuthor->id;
    }



    /**
     * Fetch all public rooms.
     */
    public function publicRooms(): JsonResponse {
        $publicRooms = Room::where('is_public', true)
            ->latest()
            ->paginate(10);

        return response()->json($publicRooms, 200);
    }
}
