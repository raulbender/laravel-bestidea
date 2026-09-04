<?php

namespace App\Actions\Ideas;

use App\Actions\Rooms\AssignAuthorToRoomAction;
use App\Models\Idea;
use App\Models\Room;
use App\Models\User;

class CreateIdeaAction
{
    public function __construct(
        private AssignAuthorToRoomAction $assignAuthorAction
    ) {}

    public function execute(Room $room, User $user, string $content): Idea
    {
        // Garante ou sorteia a persona do usuário para a sala
        $roomUser = $this->assignAuthorAction->execute($room, $user);

        return Idea::create([
            'room_id'   => $room->id,
            'user_id'   => $user->id,
            'author_id' => $roomUser->author_id,
            'content'   => $content,
        ]);
    }
}