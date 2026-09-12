<?php

namespace Tests\Feature\Api;

use App\Models\Comment;
use App\Models\Idea;
use App\Models\Rating;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RatingCommentTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_a_standard_comment_without_rating_association(): void
    {
        $user = User::factory()->create();
        $idea = Idea::factory()->create();

        $response = $this->actingAs($user)
            ->postJson("/api/ideas/{$idea->id}/comments", [
                'content' => 'Comentário simples sem nota',
                'room_uuid' => $idea->room->uuid
            ]);

        $response->assertCreated()
            ->assertJsonPath('data.content', 'Comentário simples sem nota');

        $this->assertDatabaseHas('comments', [
            'idea_id'   => $idea->id,
            'user_id'   => $user->id,
            'rating_id' => null,
            'content'   => 'Comentário simples sem nota',
        ]);
    }

    public function test_can_create_comment_attached_to_user_existing_rating(): void
    {
        $user = User::factory()->create();
        $idea = Idea::factory()->create();

        // Cria a avaliação prévia do usuário para a ideia
        $rating = Rating::factory()->create([
            'idea_id' => $idea->id,
            'user_id' => $user->id,
            'score'   => 5,
        ]);

        $response = $this->actingAs($user)
            ->postJson("/api/ideas/{$idea->id}/comments", [
                'content'       => 'Gostei muito dessa ideia!',
                'room_uuid' => $idea->room->uuid,
                'attach_rating' => true,
            ]);

        $response->assertCreated();

        $this->assertDatabaseHas('comments', [
            'idea_id'   => $idea->id,
            'user_id'   => $user->id,
            'rating_id' => $rating->id,
            'content'   => 'Gostei muito dessa ideia!',
        ]);
    }

    public function test_attach_rating_flag_handles_gracefully_when_no_rating_exists(): void
    {
        $user = User::factory()->create();
        $idea = Idea::factory()->create();

        // Nenhuma avaliação foi criada para este usuário/ideia

        $response = $this->actingAs($user)
            ->postJson("/api/ideas/{$idea->id}/comments", [
                'content'       => 'Comentário sem nota prévia',                
                'room_uuid' => $idea->room->uuid,
                'attach_rating' => true,
            ]);

        $response->assertCreated();

        $this->assertDatabaseHas('comments', [
            'idea_id'   => $idea->id,
            'user_id'   => $user->id,
            'rating_id' => null,
            'content'   => 'Comentário sem nota prévia',
        ]);
    }
}