<?php

namespace Tests\Feature\Api;

use App\Models\Idea;
use App\Models\Room;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class IdeaSecurityTest extends TestCase {
    use RefreshDatabase;

    public function test_it_should_fail_validation_when_room_uuid_is_missing_on_ideas_index(): void {
        // Cenário: Existem ideias cadastradas no sistema
        $room = Room::factory()->create(['is_public' => true]);
        Idea::factory()->count(3)->create(['room_id' => $room->id]);

        // Ação: Tentativa de listar ideias sem especificar nenhuma sala
        $response = $this->getJson('/api/ideas');

        // Assertiva esperada para o código corrigido: 422 Unprocessable Entity (ou 400 Bad Request)
        // No código ATUAL, esse teste VAI FALHAR porque retorna status 200 listando todas as ideias!
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['room_uuid']);
    }

    public function test_it_should_prevent_access_to_ideas_from_a_private_room_without_authorization(): void {
        // Cenário 1: Uma sala privada com ideias
        $privateRoom = Room::factory()->create(['is_public' => false]);
        $privateIdea = Idea::factory()->create([
            'room_id' => $privateRoom->id,
            'content' => 'Conteúdo ultrasecreto da sala privada',
        ]);

        // Cenário 2: Um usuário não autorizado / não participante
        $unauthorizedUser = User::factory()->create();

        // Ação: O usuário tenta acessar a listagem passando o identificador da sala privada
        $response = $this->actingAs($unauthorizedUser)
            ->getJson("/api/ideas?room_uuid={$privateRoom->uuid}");

        // Assertiva esperada para o código corrigido: 403 Forbidden
        // No código ATUAL, esse teste VAI FALHAR porque retorna 200 OK com os dados da sala privada!
        $response->assertStatus(403);
        $response->assertJsonMissing(['content' => 'Conteúdo ultrasecreto da sala privada']);
    }

    public function test_it_allows_fetching_ideas_from_a_public_room_with_valid_uuid(): void {
        // Cenário: Uma sala pública com ideias
        $publicRoom = Room::factory()->create(['is_public' => true]);
        $idea = Idea::factory()->create([
            'room_id' => $publicRoom->id,
            'content' => 'Ideia pública visível a todos',
        ]);

        // Ação: Acesso com UUID da sala pública
        $response = $this->getJson("/api/ideas?room_uuid={$publicRoom->uuid}");

        // Assertiva: Deve retornar 200 OK e a ideia pertencente a ela
        $response->assertStatus(200)
            ->assertJsonFragment(['content' => 'Ideia pública visível a todos']);
    }


    public function test_it_should_fail_validation_when_room_uuid_format_is_invalid(): void {
        // Envia uma string qualquer que não possui o formato de UUID
        $response = $this->getJson('/api/ideas?room_uuid=not-a-valid-uuid');

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['room_uuid']);
    }


    public function test_it_should_fail_validation_when_room_uuid_does_not_exist(): void {
        // Envia um UUID válido na estrutura, mas inexistente no banco
        $fakeUuid = (string) \Illuminate\Support\Str::uuid();

        $response = $this->getJson("/api/ideas?room_uuid={$fakeUuid}");

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['room_uuid']);
    }
}
