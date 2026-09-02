<?php

namespace Tests\Feature\Api;

use App\Models\Room;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class IdeaCreationTest extends TestCase {
    use RefreshDatabase;

    /**
     * Test that a user can post an idea directly to a room.
     */
    public function test_user_can_create_an_idea_in_a_room(): void {
        $this->seed(\Database\Seeders\AuthorSeeder::class);

        $room = Room::factory()->create();

        $payload = [
            'content' => 'Adicionar autenticação via WebAuthn sem senha',
        ];

        // Faz o POST direto (simulando API cliente sem GET prévio)
        $response = $this->postJson("/api/rooms/{$room->uuid}/ideas", $payload);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'content',
                    'author' => [
                        'name',
                        'avatar',
                        'type',
                    ],
                    'created_at',
                ],
            ]);

        // Garante que a ideia foi gravada no banco
        $this->assertDatabaseHas('ideas', [
            'room_id' => $room->id,
            'content' => 'Adicionar autenticação via WebAuthn sem senha',
        ]);

        // Garante que a persona foi criada automaticamente no pivot
        $this->assertDatabaseHas('room_users', [
            'room_id' => $room->id,
        ]);
    }

    /**
     * Test validation rules for idea creation.
     */
    public function test_cannot_create_idea_without_content(): void {
        $room = Room::factory()->create();

        $response = $this->postJson("/api/rooms/{$room->uuid}/ideas", []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['content']);
    }

    public function test_idea_is_associated_with_users_assigned_room_author(): void {
        $this->seed(\Database\Seeders\AuthorSeeder::class);

        $room = Room::factory()->create();

        // 1. Visitante faz GET para garantir que recebe um autor (ex: Raposa)
        $roomResponse = $this->getJson("/api/rooms/{$room->uuid}");
        $assignedAuthorName = $roomResponse->json('data.my_persona.name');

        // 2. Visitante envia a ideia
        $ideaResponse = $this->postJson("/api/rooms/{$room->uuid}/ideas", [
            'content' => 'Implementar login via Passkeys',
        ]);

        // 3. Assert: Garante que o autor da ideia criada é o mesmo sorteado na entrada da sala
        $ideaResponse->assertStatus(201)
            ->assertJsonPath('data.author.name', $assignedAuthorName);
    }
}
