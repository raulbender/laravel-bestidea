<?php

namespace Tests\Unit\Actions\Comments;

use App\Actions\Comments\CreateCommentAction;
use App\Models\Author;
use App\Models\Idea;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CreateCommentActionTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_creates_comment_and_increments_comments_count(): void
    {
        // 1. Arrange
        $user = User::factory()->create();
        $author = Author::factory()->create(['type' => 0]);
        $idea = Idea::factory()->create(['comments_count' => 0]);

        // 2. Act
        $action = app(CreateCommentAction::class);
        $comment = $action->execute($idea, $user, 'Excelente sugestão!');

        // 3. Assert
        $this->assertEquals('Excelente sugestão!', $comment->content);
        $this->assertEquals($user->id, $comment->user_id);
        $this->assertEquals($idea->id, $comment->idea_id);
        
        // Garante que o contador da ideia subiu de 0 para 1
        $this->assertDatabaseHas('ideas', [
            'id' => $idea->id,
            'comments_count' => 1,
        ]);
    }
}