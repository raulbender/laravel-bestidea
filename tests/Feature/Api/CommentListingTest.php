<?php

namespace Tests\Feature\Api;

use App\Models\Author;
use App\Models\Comment;
use App\Models\Idea;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CommentListingTest extends TestCase {
    use RefreshDatabase;

    public function test_can_list_comments_paginated_for_an_idea(): void {
        $user = User::factory()->create();
        $idea = Idea::factory()->create();

        Comment::factory()->count(15)->create([
            'idea_id'   => $idea->id,
        ]);

        $response = $this->actingAs($user)
            ->getJson("/api/ideas/{$idea->id}/comments");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'content',
                        'author' => ['name', 'avatar', 'type'],
                        'created_at',
                    ],
                ],
                'links' => ['first', 'last', 'prev', 'next'],
                'meta' => [
                    'current_page',
                    'from',
                    'last_page',
                    'per_page',
                    'to',
                    'total',
                ],
            ]);

        $this->assertCount(10, $response->json('data'));
    }

    public function test_only_returns_comments_belonging_to_the_specified_idea(): void {
        $user = User::factory()->create();

        $targetIdea = Idea::factory()->create();
        $otherIdea  = Idea::factory()->create();

        $commentA = Comment::factory()->create(['idea_id' => $targetIdea->id]);
        $commentB = Comment::factory()->create(['idea_id' => $otherIdea->id]);

        $response = $this->actingAs($user)
            ->getJson("/api/ideas/{$targetIdea->id}/comments");

        $response->assertStatus(200);
        $this->assertCount(1, $response->json('data'));
        $this->assertEquals($commentA->id, $response->json('data.0.id'));
    }

    public function test_returns_404_when_listing_comments_for_non_existing_idea(): void {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->getJson('/api/ideas/999999/comments');

        $response->assertStatus(404);
    }
}
