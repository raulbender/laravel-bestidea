<?php

namespace Tests\Feature\Api;

use App\Models\Idea;
use App\Models\Room;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RatingSecurityTest extends TestCase
{
    use RefreshDatabase;

    /*
    |--------------------------------------------------------------------------
    | Isolamento e Autorização de Sala (room_uuid)
    |--------------------------------------------------------------------------
    */

    public function test_prevents_submitting_rating_without_room_uuid(): void
    {
        $user = User::factory()->create();
        $room = Room::factory()->create(['is_public' => false]);
        $idea = Idea::factory()->create(['room_id' => $room->id]);

        $response = $this->actingAs($user)
            ->postJson("/api/ideas/{$idea->id}/ratings", [
                'score' => 5,
            ]);

        $response->assertStatus(403);
        $this->assertDatabaseMissing('ratings', ['idea_id' => $idea->id]);
    }

    public function test_prevents_submitting_rating_with_mismatched_room_uuid(): void
    {
        $user = User::factory()->create();
        $roomA = Room::factory()->create();
        $roomB = Room::factory()->create();

        $ideaInRoomA = Idea::factory()->create(['room_id' => $roomA->id]);

        $response = $this->actingAs($user)
            ->postJson("/api/ideas/{$ideaInRoomA->id}/ratings", [
                'room_uuid' => $roomB->uuid,
                'score'     => 4,
            ]);

        $response->assertStatus(403);
        $this->assertDatabaseMissing('ratings', ['idea_id' => $ideaInRoomA->id]);
    }

    public function test_allows_submitting_rating_with_room_uuid_in_json_payload(): void
    {
        $user = User::factory()->create();
        $room = Room::factory()->create(['is_public' => false]);
        $idea = Idea::factory()->create(['room_id' => $room->id]);

        $response = $this->actingAs($user)
            ->postJson("/api/ideas/{$idea->id}/ratings", [
                'room_uuid' => $room->uuid,
                'score'     => 5,
            ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('ratings', [
            'idea_id' => $idea->id,
            'user_id' => $user->id,
            'score'   => 5,
        ]);
    }

    public function test_allows_submitting_rating_with_room_uuid_in_query_string(): void
    {
        $user = User::factory()->create();
        $room = Room::factory()->create(['is_public' => false]);
        $idea = Idea::factory()->create(['room_id' => $room->id]);

        $response = $this->actingAs($user)
            ->postJson("/api/ideas/{$idea->id}/ratings?room_uuid={$room->uuid}", [
                'score' => 4,
            ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('ratings', [
            'idea_id' => $idea->id,
            'user_id' => $user->id,
            'score'   => 4,
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Permissões de Convidado (Guest) e Validação
    |--------------------------------------------------------------------------
    */

    public function test_prevents_guest_user_from_submitting_feedback_in_public_room(): void
    {
        $guestUser = User::factory()->create(['is_guest' => true]);
        $room = Room::factory()->create(['is_public' => true]);
        $idea = Idea::factory()->create(['room_id' => $room->id]);

        $response = $this->actingAs($guestUser)
            ->postJson("/api/ideas/{$idea->id}/ratings?room_uuid={$room->uuid}", [
                'score'    => 5,
                'feedback' => 'Feedback não permitido para visitantes em salas públicas',
            ]);

        $response->assertStatus(403);
        $this->assertDatabaseMissing('ratings', ['idea_id' => $idea->id]);
    }

    // public function test_allows_guest_user_to_submit_score_without_feedback_in_public_room(): void
    // {
    //     $guestUser = User::factory()->create(['is_guest' => true]);
    //     $room = Room::factory()->create(['is_public' => true]);
    //     $idea = Idea::factory()->create(['room_id' => $room->id]);

    //     $response = $this->actingAs($guestUser)
    //         ->postJson("/api/ideas/{$idea->id}/ratings?room_uuid={$room->uuid}", [
    //             'score' => 4,
    //         ]);

    //     $response->assertStatus(201);
    //     $this->assertDatabaseHas('ratings', [
    //         'idea_id' => $idea->id,
    //         'user_id' => $guestUser->id,
    //         'score'   => 4,
    //     ]);
    // }

    public function test_validates_score_range_on_rating_creation(): void
    {
        $user = User::factory()->create();
        $room = Room::factory()->create(['is_public' => true]);
        $idea = Idea::factory()->create(['room_id' => $room->id]);

        $response = $this->actingAs($user)
            ->postJson("/api/ideas/{$idea->id}/ratings?room_uuid={$room->uuid}", [
                'score' => 6,
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['score']);
    }

    public function test_returns_404_when_rating_non_existent_idea(): void
    {
        $user = User::factory()->create();
        $room = Room::factory()->create();

        $response = $this->actingAs($user)
            ->postJson("/api/ideas/99999/ratings?room_uuid={$room->uuid}", [
                'score' => 5,
            ]);

        $response->assertStatus(404);
    }

    /*
    |--------------------------------------------------------------------------
    | Segurança da Listagem de Avaliações (GET /api/ideas/{id}/ratings)
    |--------------------------------------------------------------------------
    */

    public function test_prevents_listing_ratings_without_room_uuid(): void
    {
        $room = Room::factory()->create(['is_public' => false]);
        $idea = Idea::factory()->create(['room_id' => $room->id]);

        $response = $this->getJson("/api/ideas/{$idea->id}/ratings");

        $response->assertStatus(403);
    }

    public function test_prevents_listing_ratings_with_mismatched_room_uuid(): void
    {
        $roomA = Room::factory()->create();
        $roomB = Room::factory()->create();

        $ideaInRoomA = Idea::factory()->create(['room_id' => $roomA->id]);

        $response = $this->getJson("/api/ideas/{$ideaInRoomA->id}/ratings?room_uuid={$roomB->uuid}");

        $response->assertStatus(403);
    }

    public function test_allows_listing_ratings_with_valid_room_uuid(): void
    {
        $room = Room::factory()->create();
        $idea = Idea::factory()->create(['room_id' => $room->id]);

        $response = $this->getJson("/api/ideas/{$idea->id}/ratings?room_uuid={$room->uuid}");

        $response->assertStatus(200)
            ->assertJsonStructure(['data']);
    }

    public function test_returns_404_when_listing_ratings_of_non_existent_idea(): void
    {
        $room = Room::factory()->create();

        $response = $this->getJson("/api/ideas/99999/ratings?room_uuid={$room->uuid}");

        $response->assertStatus(404);
    }
}