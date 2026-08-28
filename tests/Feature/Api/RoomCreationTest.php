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
            ->assertJsonValidationErrors(['description', 'expires_at']);
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


}
