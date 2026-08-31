<?php

namespace Tests\Feature\Api;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\Room;

class RoomCreationTest extends TestCase {
    use RefreshDatabase;


    /**
     * A basic feature test example.
     */
    public function test_example(): void {
        $response = $this->get('/');

        $response->assertStatus(200);
    }


    public function test_can_create_a_new_room_via_api(): void {
        // 1. Arrange: Define request payload
        $payload = [
            'description' => 'Laravel Migration Room',
            'expires_at'  => now()->addHours(24)->toDateTimeString(),
        ];

        // 2. Act: Send POST request to API
        $response = $this->postJson('/api/rooms', $payload);

        // 3. Assert: Verify response and database persistence
        $response->assertStatus(201)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'uuid',
                    'description',
                    'expires_at',
                    'created_at',
                ],
            ]);

        $this->assertDatabaseHas('rooms', [
            'description' => 'Laravel Migration Room',
        ]);
    }



    /**
     * Test validation rules when required fields are missing.
     */
    public function test_cannot_create_room_without_required_fields(): void {
        $response = $this->postJson('/api/rooms', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['description']);
    }



    /**
     * Test that every created room receives an automatic UUID.
     */
    public function test_created_room_has_a_valid_uuid(): void {
        $payload = [
            'description' => 'UUID Test Room',
            'expires_at'  => now()->addDay()->toDateTimeString(),
        ];

        $response = $this->postJson('/api/rooms', $payload);

        $room = Room::first();

        $this->assertNotNull($room->uuid);
        $this->assertIsString($room->uuid);
    }

    /**
     * Test fetching a room by its UUID.
     */
    public function test_can_fetch_a_room_by_uuid(): void {
        // 1. Arrange: Create a room using the model
        $room = \App\Models\Room::create([
            'description' => 'Brainstorming Session',
            'expires_at'  => now()->addHours(2)->toDateTimeString(),
        ]);

        // 2. Act: Request the room by its auto-generated UUID
        $response = $this->getJson("/api/rooms/{$room->uuid}");

        // 3. Assert: Verify status code and payload structure
        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    'uuid'        => $room->uuid,
                    'description' => 'Brainstorming Session',
                ],
            ]);
    }

    /**
     * Test requesting a non-existent room UUID returns 404.
     */
    public function test_returns_404_when_room_uuid_not_found(): void {
        $response = $this->getJson('/api/rooms/non-existent-uuid-1234');

        $response->assertStatus(404);
    }



    /**
     * Test creating public rooms and verifying they appear on the homepage/public endpoint.
     */
    public function test_public_rooms_are_listed_on_the_homepage(): void {
        // 1. Act: Create a public room via the API
        $publicPayload = [
            'description' => 'Open Innovation Session',
            'is_public'   => true,
        ];

        $this->postJson('/api/rooms', $publicPayload)
            ->assertStatus(201);

        // 2. Act: Create a private room via the API
        $privatePayload = [
            'description' => 'Secret Internal Roadmap',
            'is_public'   => false,
        ];

        $this->postJson('/api/rooms', $privatePayload)
            ->assertStatus(201);

        // 3. Act: Fetch the homepage/public listing
        $response = $this->getJson('/api/rooms/public');

        // 4. Assert: Only the public room should appear in the results
        $response->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonFragment([
                'description' => 'Open Innovation Session',
                'is_public'   => true,
            ])
            ->assertJsonMissing([
                'description' => 'Secret Internal Roadmap',
            ]);
    }
}
