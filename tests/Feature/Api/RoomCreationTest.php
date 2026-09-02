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
        $this->seed(\Database\Seeders\AuthorSeeder::class);
        // 1. Arrange: Create a room using the model
        $user = \App\Models\User::factory()->create();

        $room = Room::create([
            'description' => 'Brainstorming Session',
            'expires_at'  => now()->addHours(2)->toDateTimeString(),
            'user_id'     => $user->id,
        ]);

        // 2. Act: Request the room by its auto-generated UUID
        $response = $this->getJson("/api/rooms/{$room->uuid}");

        // 3. Assert: Verify status code and payload structure
        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    'room' => [
                        'uuid'        => $room->uuid,
                        'description' => 'Brainstorming Session',
                    ],
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


    /**
     * Test that fetching a room assigns a random animal author persona to the visitor.
     */
    public function test_fetching_a_room_assigns_an_animal_author_persona_to_the_user(): void {
        // 1. Arrange: Executa a Seeder de autores (animais/frutas)
        $this->seed(\Database\Seeders\AuthorSeeder::class);

        $owner = \App\Models\User::factory()->create();
        $room = Room::create([
            'description' => 'TDD Brainstorming Room',
            'expires_at'  => now()->addHours(5),
            'user_id'     => $owner->id,
        ]);

        // 2. Act: Faz a requisição para obter a sala sem estar logado (Guest criado pelo middleware)
        $response = $this->getJson("/api/rooms/{$room->uuid}");

        // 3. Assert: Verifica se a persona foi sorteada e anexada ao contrato JSON
        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'room',
                    'my_persona' => [
                        'name',
                        'avatar',
                        'type',
                    ],
                    'is_owner',
                ],
            ])
            ->assertJsonPath('data.my_persona.type', 0) // Deve ser Humano (Animal)
            ->assertJsonPath('data.is_owner', false);

        // Verifica se o registro foi salvo na tabela room_users
        $this->assertDatabaseHas('room_users', [
            'room_id' => $room->id,
        ]);
    }

    /**
     * Test that accessing the room again reuses the previously assigned persona.
     */
    public function test_user_retains_same_assigned_author_persona_on_subsequent_views(): void {
        $this->seed(\Database\Seeders\AuthorSeeder::class);

        $owner = \App\Models\User::factory()->create();
        $room = Room::create([
            'description' => 'Persistent Persona Room',
            'expires_at'  => now()->addHours(5),
            'user_id'     => $owner->id,
        ]);

        // Primeira visita
        $firstResponse = $this->getJson("/api/rooms/{$room->uuid}");
        $assignedPersona = $firstResponse->json('data.my_persona');

        // Segunda visita (mesmo contexto/cookies de sessão no teste)
        $secondResponse = $this->getJson("/api/rooms/{$room->uuid}");

        // Deve manter o mesmo animal sorteado na primeira requisição
        $secondResponse->assertStatus(200)
            ->assertJsonPath('data.my_persona.name', $assignedPersona['name']);
    }
}
