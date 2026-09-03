<?php

namespace Tests\Feature\Api;

use App\Models\Idea;
use App\Models\User;
use App\Models\Room;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class IdeaListingTest extends TestCase {
    use RefreshDatabase;

    public function test_can_list_ideas_paginated(): void {
        $user = User::factory()->create();
        Idea::factory()->count(15)->create();

        $response = $this->actingAs($user)
            ->getJson('/api/ideas');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'content', 'total_score', 'ratings_count', 'avg_score', 'created_at'],
                ],
                'current_page',
                'per_page',
                'total',
                'last_page',
                'links',
            ]);

        $this->assertCount(10, $response->json('data'));
    }


    public function test_user_can_filter_only_their_own_ideas_in_a_room(): void {
        $room = Room::factory()->create();
        $userA = User::factory()->create();
        $userB = User::factory()->create();

        $ideaA1 = Idea::factory()->create(['room_id' => $room->id, 'user_id' => $userA->id]);
        $ideaA2 = Idea::factory()->create(['room_id' => $room->id, 'user_id' => $userA->id]);
        $ideaB1 = Idea::factory()->create(['room_id' => $room->id, 'user_id' => $userB->id]);

        // Filtra passando a flag 'mine=1' e o 'room_id'
        $response = $this->actingAs($userA)
            ->getJson("/api/ideas?room_id={$room->id}&filter=mine");

        $response->assertStatus(200);
        $this->assertCount(2, $response->json('data'));
        $this->assertEquals($ideaA2->id, $response->json('data.0.id'));
        $this->assertEquals($ideaA1->id, $response->json('data.1.id'));
    }


    public function test_can_filter_ideas_by_top_rated(): void {
        $user = User::factory()->create();

        $ideaLow = Idea::factory()->create(['avg_score' => 2.00, 'ratings_count' => 5]);

        // Mesma média (4.80), mas $ideaHighHasMoreRatings tem mais votos e deve desempatar em 1º lugar
        $ideaHighEqualAvg = Idea::factory()->create(['avg_score' => 4.80, 'ratings_count' => 3]);
        $ideaHighHasMoreRatings = Idea::factory()->create(['avg_score' => 4.80, 'ratings_count' => 10]);

        $response = $this->actingAs($user)
            ->getJson('/api/ideas?sort=top_rated');

        $response->assertStatus(200);
        $this->assertEquals($ideaHighHasMoreRatings->id, $response->json('data.0.id'));
        $this->assertEquals($ideaHighEqualAvg->id, $response->json('data.1.id'));
        $this->assertEquals($ideaLow->id, $response->json('data.2.id'));
    }

    public function test_can_filter_ideas_by_recent(): void {
        $user = User::factory()->create();

        $oldIdea = Idea::factory()->create(['created_at' => now()->subDays(5)]);
        $newIdea = Idea::factory()->create(['created_at' => now()]);

        $response = $this->actingAs($user)
            ->getJson('/api/ideas?sort=recent');

        $response->assertStatus(200);
        $this->assertEquals($newIdea->id, $response->json('data.0.id'));
        $this->assertEquals($oldIdea->id, $response->json('data.1.id'));
    }



    public function test_can_filter_ideas_by_hot(): void {
        $user = User::factory()->create();

        // 1. Ideia recente mas com engajamento médio (ID menor)
        $recentMediumScore = Idea::factory()->create([
            'total_score' => 30,
            'created_at'  => now()->subDays(1),
        ]);

        // 2. Ideia RECENTE e MUITO ENGAJADA -> DEVE SER A 1ª COLOCADA
        $recentHighScore = Idea::factory()->create([
            'total_score' => 80,
            'created_at'  => now()->subDays(2),
        ]);

        // 3. Ideia ANTIGA (fora da janela de 30 dias) com score altíssimo
        // Mesmo tendo score 200, por ser antiga deve ficar abaixo ou ser desconsiderada
        $oldHighScore = Idea::factory()->create([
            'total_score' => 200,
            'created_at'  => now()->subDays(45),
        ]);

        // 4. Ideia recente com nota zero (ID maior)
        $recentZeroScore = Idea::factory()->create([
            'total_score' => 0,
            'created_at'  => now()->subMinutes(10),
        ]);

        $response = $this->actingAs($user)
            ->getJson('/api/ideas?sort=hot');

        $response->assertStatus(200);

        // Garante que a 1ª é a ideia recente de alto engajamento
        $this->assertEquals($recentHighScore->id, $response->json('data.0.id'));

        // Garante que a 2ª é a recente de médio engajamento
        $this->assertEquals($recentMediumScore->id, $response->json('data.1.id'));

        // Garante que a ideia recente com nota 0 ficou depois das engajadas
        $this->assertEquals($recentZeroScore->id, $response->json('data.2.id'));

        // Garante que a ideia antiga de alto engajamento ficou por último (ou desconsiderada)
        $this->assertEquals($oldHighScore->id, $response->json('data.3.id'));
    }
}
