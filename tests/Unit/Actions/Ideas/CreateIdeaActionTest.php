<?php

namespace Tests\Unit\Actions\Ideas;

use App\Actions\Ideas\CreateIdeaAction;
use App\Models\Author;
use App\Models\Room;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CreateIdeaActionTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_creates_an_idea_assigned_to_user_and_author_in_room(): void
    {
        // 1. Arrange
        $user = User::factory()->create();
        $room = Room::factory()->create();
        $author = Author::factory()->create(['type' => 0]);

        // 2. Act
        $action = app(CreateIdeaAction::class);
        $idea = $action->execute($room, $user, 'Minha nova ideia para o projeto');

        // 3. Assert
        $this->assertEquals('Minha nova ideia para o projeto', $idea->content);
        $this->assertEquals($room->id, $idea->room_id);
        $this->assertEquals($user->id, $idea->user_id);
        $this->assertEquals($author->id, $idea->author_id);

        $this->assertDatabaseHas('ideas', [
            'room_id' => $room->id,
            'user_id' => $user->id,
            'author_id' => $author->id,
            'content' => 'Minha nova ideia para o projeto',
        ]);
    }
}