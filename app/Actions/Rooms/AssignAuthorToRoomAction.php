<?php

namespace App\Actions\Rooms;

use App\Models\Author;
use App\Models\Room;
use App\Models\RoomUser;
use App\Models\User;

class AssignAuthorToRoomAction
{
    public function execute(Room $room, User $user): RoomUser
    {
        return RoomUser::firstOrCreate(
            [
                'room_id' => $room->id,
                'user_id' => $user->id,
            ],
            [
                'author_id' => $this->getAvailableAuthorId($room->id),
            ]
        );
    }

    private function getAvailableAuthorId(int $roomId): int
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