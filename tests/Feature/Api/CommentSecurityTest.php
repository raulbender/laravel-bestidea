<?php

namespace Tests\Feature\Api;

use App\Models\Comment;
use App\Models\Idea;
use App\Models\Room;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CommentSecurityTest extends TestCase 
{
    use RefreshDatabase;

    /*
    |--------------------------------------------------------------------------
    | Testes de Leitura (GET /api/ideas/{id}/comments)
    |--------------------------------------------------------------------------
    */

    public function test_prevents_fetching_comments_without_room_uuid(): void 
    {
        $user = User::factory()->create();
        $room = Room::factory()->create(['is_public' => false]);
        $idea = Idea::factory()->create(['room_id' => $room->id]);

        Comment::factory()->create([
            'idea_id' => $idea->id,
            'content' => 'Comentário confidencial',
        ]);

        $response = $this->actingAs($user)
            ->getJson("/api/ideas/{$idea->id}/comments");

        $response->assertStatus(403);
        $response->assertJsonMissing(['content' => 'Comentário confidencial']);
    }

    public function test_prevents_fetching_comments_with_mismatched_room_uuid(): void 
    {
        $user = User::factory()->create();
        $roomA = Room::factory()->create();
        $roomB = Room::factory()->create();

        $ideaInRoomA = Idea::factory()->create(['room_id' => $roomA->id]);

        Comment::factory()->create([
            'idea_id' => $ideaInRoomA->id,
            'content' => 'Comentário da Sala A',
        ]);

        $response = $this->actingAs($user)
            ->getJson("/api/ideas/{$ideaInRoomA->id}/comments?room_uuid={$roomB->uuid}");

        $response->assertStatus(403);
        $response->assertJsonMissing(['content' => 'Comentário da Sala A']);
    }

    public function test_allows_fetching_comments_when_valid_room_uuid_is_provided(): void 
    {
        $user = User::factory()->create();
        $room = Room::factory()->create(['is_public' => false]);
        $idea = Idea::factory()->create(['room_id' => $room->id]);

        $comment = Comment::factory()->create([
            'idea_id' => $idea->id,
            'content' => 'Comentário autorizado via UUID',
        ]);

        $response = $this->actingAs($user)
            ->getJson("/api/ideas/{$idea->id}/comments?room_uuid={$room->uuid}");

        $response->assertStatus(200);
        $response->assertJsonFragment(['id' => $comment->id, 'content' => 'Comentário autorizado via UUID']);
    }

    public function test_does_not_leak_comments_from_other_ideas_in_same_room(): void 
    {
        $user = User::factory()->create();
        $room = Room::factory()->create(['is_public' => true]);

        $ideaA = Idea::factory()->create(['room_id' => $room->id]);
        $ideaB = Idea::factory()->create(['room_id' => $room->id]);

        $commentA = Comment::factory()->create(['idea_id' => $ideaA->id, 'content' => 'Ideia A']);
        $commentB = Comment::factory()->create(['idea_id' => $ideaB->id, 'content' => 'Ideia B']);

        $response = $this->actingAs($user)
            ->getJson("/api/ideas/{$ideaA->id}/comments?room_uuid={$room->uuid}");

        $response->assertStatus(200);
        $this->assertCount(1, $response->json('data'));
        $response->assertJsonFragment(['id' => $commentA->id]);
        $response->assertJsonMissing(['id' => $commentB->id]);
    }

    public function test_returns_404_when_fetching_comments_for_non_existent_idea(): void 
    {
        $user = User::factory()->create();
        $room = Room::factory()->create();

        $response = $this->actingAs($user)
            ->getJson("/api/ideas/99999/comments?room_uuid={$room->uuid}");

        $response->assertStatus(404);
    }

    /*
    |--------------------------------------------------------------------------
    | Testes de Criação (POST /api/ideas/{id}/comments)
    |--------------------------------------------------------------------------
    */

    public function test_prevents_creating_comment_without_room_uuid(): void 
    {
        $user = User::factory()->create();
        $room = Room::factory()->create(['is_public' => false]);
        $idea = Idea::factory()->create(['room_id' => $room->id]);

        $response = $this->actingAs($user)
            ->postJson("/api/ideas/{$idea->id}/comments", [
                'content' => 'Injeção sem chave da sala',
            ]);

        $response->assertStatus(403);
        $this->assertDatabaseMissing('comments', ['content' => 'Injeção sem chave da sala']);
    }

    public function test_prevents_creating_comment_with_mismatched_room_uuid(): void 
    {
        $user = User::factory()->create();
        $roomA = Room::factory()->create();
        $roomB = Room::factory()->create();

        $ideaInRoomA = Idea::factory()->create(['room_id' => $roomA->id]);

        $response = $this->actingAs($user)
            ->postJson("/api/ideas/{$ideaInRoomA->id}/comments", [
                'room_uuid' => $roomB->uuid,
                'content'   => 'Tentativa de enviar com chave errada',
            ]);

        $response->assertStatus(403);
        $this->assertDatabaseMissing('comments', ['content' => 'Tentativa de enviar com chave errada']);
    }

    public function test_allows_creating_comment_with_room_uuid_in_json_payload(): void 
    {
        $user = User::factory()->create();
        $room = Room::factory()->create(['is_public' => false]);
        $idea = Idea::factory()->create(['room_id' => $room->id]);

        $response = $this->actingAs($user)
            ->postJson("/api/ideas/{$idea->id}/comments", [
                'room_uuid' => $room->uuid,
                'content'   => 'Comentário via JSON Payload',
            ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('comments', [
            'idea_id' => $idea->id,
            'content' => 'Comentário via JSON Payload',
        ]);
    }

    public function test_allows_creating_comment_with_room_uuid_in_query_string(): void 
    {
        $user = User::factory()->create();
        $room = Room::factory()->create(['is_public' => false]);
        $idea = Idea::factory()->create(['room_id' => $room->id]);

        $response = $this->actingAs($user)
            ->postJson("/api/ideas/{$idea->id}/comments?room_uuid={$room->uuid}", [
                'content' => 'Comentário via Query String',
            ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('comments', [
            'idea_id' => $idea->id,
            'content' => 'Comentário via Query String',
        ]);
    }

    public function test_prevents_guest_user_from_commenting_in_public_room(): void 
    {
        $guestUser = User::factory()->create(['is_guest' => true]);
        $room = Room::factory()->create(['is_public' => true]);
        $idea = Idea::factory()->create(['room_id' => $room->id]);

        $response = $this->actingAs($guestUser)
            ->postJson("/api/ideas/{$idea->id}/comments?room_uuid={$room->uuid}", [
                'content' => 'Guest tentando comentar em sala pública',
            ]);

        $response->assertStatus(403);
        $this->assertDatabaseMissing('comments', ['content' => 'Guest tentando comentar em sala pública']);
    }

    public function test_validates_comment_payload_on_creation(): void 
    {
        $user = User::factory()->create();
        $room = Room::factory()->create(['is_public' => true]);
        $idea = Idea::factory()->create(['room_id' => $room->id]);

        $response = $this->actingAs($user)
            ->postJson("/api/ideas/{$idea->id}/comments?room_uuid={$room->uuid}", []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['content']);
    }

    public function test_returns_404_when_creating_comment_on_non_existent_idea(): void 
    {
        $user = User::factory()->create();
        $room = Room::factory()->create();

        $response = $this->actingAs($user)
            ->postJson("/api/ideas/99999/comments?room_uuid={$room->uuid}", [
                'content' => 'Comentário em ideia inexistente',
            ]);

        $response->assertStatus(404);
    }
}