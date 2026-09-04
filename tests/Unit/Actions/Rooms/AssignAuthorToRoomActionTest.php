<?php

namespace Tests\Unit\Actions\Rooms;

use App\Actions\Rooms\AssignAuthorToRoomAction;
use App\Models\Author;
use App\Models\Room;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AssignAuthorToRoomActionTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_assigns_an_available_author_persona_to_user_in_a_room(): void
    {
        // 1. Arrange: Cria usuário, sala e autor disponível (type = 0)
        $user = User::factory()->create();
        $room = Room::factory()->create();
        $author = Author::factory()->create(['type' => 0]);

        // 2. Act: Executa a Action isoladamente
        $action = new AssignAuthorToRoomAction();
        $roomUser = $action->execute($room, $user);

        // 3. Assert: Verifica se o vínculo no pivot room_users foi criado corretamente
        $this->assertEquals($room->id, $roomUser->room_id);
        $this->assertEquals($user->id, $roomUser->user_id);
        $this->assertEquals($author->id, $roomUser->author_id);
        
        $this->assertDatabaseHas('room_users', [
            'room_id'   => $room->id,
            'user_id'   => $user->id,
            'author_id' => $author->id,
        ]);
    }

    public function test_it_reuses_already_assigned_author_if_user_revisits_room(): void
    {
        // 1. Arrange
        $user = User::factory()->create();
        $room = Room::factory()->create();
        Author::factory()->count(3)->create(['type' => 0]);

        $action = new AssignAuthorToRoomAction();

        // 2. Act: Primeira execução sorteia uma persona
        $firstAssignment = $action->execute($room, $user);

        // Segunda execução para o mesmo usuário na mesma sala
        $secondAssignment = $action->execute($room, $user);

        // 3. Assert: Garante que manteve o mesmo ID de persona sem duplicar
        $this->assertEquals($firstAssignment->author_id, $secondAssignment->author_id);
        $this->assertDatabaseCount('room_users', 1);
    }
}