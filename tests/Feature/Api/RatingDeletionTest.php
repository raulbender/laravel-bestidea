<?php

namespace Tests\Feature\Api;

use App\Models\Idea;
use App\Models\Rating;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RatingDeletionTest extends TestCase {
    use RefreshDatabase;

    public function test_user_can_delete_their_own_rating(): void {
        // 1. Arrange
        $user = User::factory()->create();
        $idea = Idea::factory()->create([
            'ratings_count' => 1,
            'total_score'   => 5,
            'avg_score'     => 5.00,
        ]);

        Rating::factory()->create([
            'idea_id' => $idea->id,
            'user_id' => $user->id,
            'score'   => 5,
        ]);

        $roomUuid = $idea->room->uuid;

        // 2. Act
        $response = $this->actingAs($user)
            ->deleteJson("/api/ideas/{$idea->id}/ratings?room_uuid={$roomUuid}");

        // 3. Assert
        $response->assertStatus(200)
            ->assertJson(['message' => 'Rating removed successfully.']);

        // Valida que o registro sumiu do banco
        $this->assertDatabaseMissing('ratings', [
            'idea_id' => $idea->id,
            'user_id' => $user->id,
        ]);

        // Valida que os agregados da ideia foram zerados
        $idea->refresh();
        $this->assertEquals(0, $idea->ratings_count);
        $this->assertEquals(0, $idea->total_score);
        $this->assertEquals(0.00, $idea->avg_score);
    }

    public function test_deleting_rating_requires_room_uuid(): void {
        $user = User::factory()->create();
        $idea = Idea::factory()->create();

        $response = $this->actingAs($user)
            ->deleteJson("/api/ideas/{$idea->id}/ratings");

        $response->assertStatus(403);
    }
}
