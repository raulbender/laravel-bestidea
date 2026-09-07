<?php

namespace App\Http\Controllers\Api;

use App\Actions\Rooms\AssignAuthorToRoomAction;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\RoomResource;
use App\Models\Room;
use Illuminate\Http\JsonResponse;
use App\Http\Requests\StoreRoomRequest;
use Illuminate\Support\Facades\Auth;

class RoomController extends Controller 
{
    public function store(StoreRoomRequest $request): JsonResponse 
    {

        $room = Room::create([...$request->validated(), 'user_id' => Auth::id()]);

        return (new RoomResource($room))
            ->response()
            ->setStatusCode(201);
    }

    public function show(string $uuid, AssignAuthorToRoomAction $assignAuthorAction) 
    {
        $room = Room::where('uuid', $uuid)->firstOrFail();

        // Delega a regra de sorteio e vinculo do autor para a Action
        $assignAuthorAction->execute($room, Auth::user());

        $room->load(['roomUsers.author']);

        return new RoomResource($room);
    }

    public function publicRooms() 
    {
        $rooms = Room::where('is_public', true)
        ->withCount('roomUsers')
            ->latest()
            ->paginate(10);

        return RoomResource::collection($rooms);
    }
}