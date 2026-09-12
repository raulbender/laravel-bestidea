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

    public function test_creates_new_comment_attached_to_rating_and_increments_comments_count(): void
    {
        $user = User::factory()->create();
        $idea = Idea::factory()->create(['comments_count' => 0]);
        $rating = Rating::factory()->create([
            'idea_id' => $idea->id,
            'user_id' => $user->id,
        ]);

        $response = $this->actingAs($user)
            ->postJson("/api/ideas/{$idea->id}/comments", [
                'content'       => 'Primeira avaliação com texto',
                'attach_rating' => true,
                'room_uuid' => $idea->room->uuid,
            ]);

        $response->assertCreated();

        $this->assertDatabaseHas('comments', [
            'idea_id'   => $idea->id,
            'user_id'   => $user->id,
            'rating_id' => $rating->id,
            'content'   => 'Primeira avaliação com texto',
        ]);

        $this->assertEquals(1, $idea->fresh()->comments_count);
    }

    public function test_updates_existing_rating_comment_without_incrementing_comments_count(): void
    {
        $user = User::factory()->create();
        $idea = Idea::factory()->create(['comments_count' => 1]);
        $rating = Rating::factory()->create([
            'idea_id' => $idea->id,
            'user_id' => $user->id,
        ]);
        
        $existingComment = Comment::factory()->create([
            'idea_id'   => $idea->id,
            'user_id'   => $user->id,
            'rating_id' => $rating->id,
            'content'   => 'Texto antigo do comentário',
        ]);

        $response = $this->actingAs($user)
            ->postJson("/api/ideas/{$idea->id}/comments", [
                'content'       => 'Texto atualizado do comentário',
                'attach_rating' => true,
                'room_uuid' => $idea->room->uuid,
            ]);

        $response->assertSuccessful();

        // Garante que o registro existente foi alterado
        $this->assertDatabaseHas('comments', [
            'id'      => $existingComment->id,
            'content' => 'Texto atualizado do comentário',
        ]);

        // Garante que o número de comentários no banco para essa ideia continua sendo 1
        $this->assertDatabaseCount('comments', 1);

        // Garante que a contagem na ideia NÃO subiu para 2
        $this->assertEquals(1, $idea->fresh()->comments_count);
    }

    public function test_creates_regular_comment_when_attach_rating_is_false(): void
    {
        $user = User::factory()->create();
        $idea = Idea::factory()->create(['comments_count' => 0]);

        $response = $this->actingAs($user)
            ->postJson("/api/ideas/{$idea->id}/comments", [
                'content'       => 'Comentário sem vínculo com nota',
                'room_uuid' => $idea->room->uuid,
                'attach_rating' => false,
            ]);

        $response->assertCreated();

        $this->assertDatabaseHas('comments', [
            'idea_id'   => $idea->id,
            'user_id'   => $user->id,
            'rating_id' => null,
            'content'   => 'Comentário sem vínculo com nota',
        ]);

        $this->assertEquals(1, $idea->fresh()->comments_count);
    }



    public function test_creates_regular_comment_when_attach_rating_is_not_provided(): void
    {
        $user = User::factory()->create();
        $idea = Idea::factory()->create(['comments_count' => 0]);

        $response = $this->actingAs($user)
            ->postJson("/api/ideas/{$idea->id}/comments", [
                'content'       => 'Comentário sem attach_rating',
                'room_uuid' => $idea->room->uuid,
            ]);

        $response->assertCreated();

        $this->assertDatabaseHas('comments', [
            'idea_id'   => $idea->id,
            'user_id'   => $user->id,
            'rating_id' => null,
            'content'   => 'Comentário sem attach_rating',
        ]);

        $this->assertEquals(1, $idea->fresh()->comments_count);
    }


    
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