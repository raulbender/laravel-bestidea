<?php

namespace Tests\Feature\Api;

use App\Models\Idea;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CommentCreationTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_comment_on_an_idea(): void
    {
        $user = User::factory()->create();
        $idea = Idea::factory()->create();

        $response = $this->actingAs($user)
            ->postJson("/api/ideas/{$idea->id}/comments", [
                'content' => 'Excelente sugestão de melhoria!',
            ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'content',
                    'author' => ['name', 'avatar', 'type'],
                    'created_at',
                ],
            ]);

        $this->assertDatabaseHas('comments', [
            'idea_id' => $idea->id,
            'user_id' => $user->id,
            'content' => 'Excelente sugestão de melhoria!',
        ]);

        $this->assertEquals(1, $idea->fresh()->comments_count);
    }



    public function test_cannot_comment_on_non_existing_idea(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->postJson('/api/ideas/999999/comments', [
                'content' => 'Comentário em ideia inexistente',
            ]);

        $response->assertStatus(404);
    }

    public function test_comment_content_is_required(): void
    {
        $user = User::factory()->create();
        $idea = Idea::factory()->create();

        $response = $this->actingAs($user)
            ->postJson("/api/ideas/{$idea->id}/comments", [
                'content' => '',
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['content']);
    }

    public function test_comment_content_cannot_exceed_max_length(): void
    {
        $user = User::factory()->create();
        $idea = Idea::factory()->create();

        $response = $this->actingAs($user)
            ->postJson("/api/ideas/{$idea->id}/comments", [
                'content' => str_repeat('a', 1001),
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['content']);
    }

    public function test_multiple_comments_increment_counter_correctly(): void
    {
        $user = User::factory()->create();
        $idea = Idea::factory()->create(['comments_count' => 0]);

        $this->actingAs($user)->postJson("/api/ideas/{$idea->id}/comments", ['content' => 'Primeiro']);
        $this->actingAs($user)->postJson("/api/ideas/{$idea->id}/comments", ['content' => 'Segundo']);

        $this->assertEquals(2, $idea->fresh()->comments_count);
    }
}