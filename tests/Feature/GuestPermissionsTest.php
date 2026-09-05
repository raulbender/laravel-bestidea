<?php

namespace Tests\Feature;

use App\Models\Idea;
use App\Models\Room;
use App\Models\User;
use App\Models\Author;
use App\Models\RoomUser;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GuestPermissionsTest extends TestCase {
    use RefreshDatabase;

    protected function setUp(): void {
        parent::setUp();

        // Insira alguns autores no banco para a Action utilizá-los
        Author::factory()->count(5)->create(['type' => 0]);
    }

    public function test_guest_can_rate_idea_without_feedback_in_public_room() {
        $room = Room::factory()->create(['is_public' => true]);
        $idea = Idea::factory()->for($room)->create();

        $response = $this->postJson("/api/ideas/{$idea->id}/ratings", [
            'score' => 5,
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('ratings', [
            'idea_id' => $idea->id,
            'score' => 5,
            'feedback' => null,
        ]);
    }


    public function test_guest_is_forbidden_from_creating_idea_in_public_room() {
        $room = Room::factory()->create(['is_public' => true]);

        $response = $this->postJson("/api/rooms/{$room->uuid}/ideas", [
            'content' => 'Minha ideia em sala pública',
        ]);

        $response->assertStatus(403);
    }


    public function test_guest_is_forbidden_from_commenting_in_public_room() {
        $room = Room::factory()->create(['is_public' => true]);
        $idea = Idea::factory()->for($room)->create();

        $response = $this->postJson("/api/ideas/{$idea->id}/comments", [
            'content' => 'Comentário em sala pública',
        ]);

        $response->assertStatus(403);
    }



    public function test_guest_can_create_private_room() {
        $this->postJson('/api/rooms', [
            'description' => 'Brainstorming Privado',
            'is_public' => false,
            'expires_at' => now()->addHours(12)->toIso8601String(),
        ])->assertStatus(201);
    }



    public function test_guest_can_post_ideas_in_private_room() {
        // Setup isolado: cria a sala direto pelo Eloquent
        $room = Room::factory()->create(['is_public' => false]);

        // Ação e Asserção: foca puramente no endpoint de ideias
        $this->postJson("/api/rooms/{$room->uuid}/ideas", [
            'content' => 'Ideia em sala privada',
        ])->assertStatus(201);
    }




    public function test_viewing_any_get_endpoint_does_not_persist_guest_user_or_room_user_relationship() {
        $room = Room::factory()->create(['is_public' => true]);
        $idea = Idea::factory()->for($room)->create();

        // Congela a quantidade atual dinamicamente para não depender do comportamento das factories
        $initialUserCount = User::count();
        $initialRoomUserCount = RoomUser::count();

        $this->getJson('/api/rooms/public')->assertStatus(200);
        $this->getJson("/api/rooms/{$room->uuid}")->assertStatus(200);
        $this->getJson('/api/ideas')->assertStatus(200);
        $this->getJson("/api/ideas/{$idea->id}/comments")->assertStatus(200);

        // Valida que o banco não sofreu mutações
        $this->assertDatabaseCount('users', $initialUserCount);
        $this->assertDatabaseCount('room_users', $initialRoomUserCount);
        $this->assertDatabaseMissing('users', ['is_guest' => true]);
    }


    public function test_guest_is_forbidden_from_rating_with_feedback_comment_in_public_room() {
        $room = Room::factory()->create(['is_public' => true]);
        $idea = Idea::factory()->for($room)->create();

        $response = $this->postJson("/api/ideas/{$idea->id}/ratings", [
            'score' => 4,
            'feedback' => 'Excelente ideia, parabéns!',
        ]);

        $response->assertStatus(403);
    }

    /** @test */
    public function test_guest_is_forbidden_from_creating_a_public_room() {
        $response = $this->postJson('/api/rooms', [
            'description' => 'Tentativa de criar sala pública',
            'is_public' => true,
        ]);

        $response->assertStatus(403);
    }


    public function test_guest_can_perform_full_interaction_in_private_room() {
        $room = Room::factory()->create(['is_public' => false]);
        $idea = Idea::factory()->for($room)->create();

        // 1. Criar Ideia
        $this->postJson("/api/rooms/{$room->uuid}/ideas", ['content' => 'Ideia'])->assertStatus(201);

        // 2. Comentar
        $this->postJson("/api/ideas/{$idea->id}/comments", ['content' => 'Comentário'])->assertStatus(201);

        // 3. Avaliar com Feedback
        $this->postJson("/api/ideas/{$idea->id}/ratings", [
            'score' => 5,
            'feedback' => 'Feedback liberado em sala privada',
        ])->assertStatus(201);
    }




    public function test_guest_cannot_create_private_room_with_expiration_exceeding_24_hours() {
        $response = $this->postJson('/api/rooms', [
            'description' => 'Sala muito longa',
            'is_public' => false,
            'expires_at' => now()->addHours(25)->toIso8601String(),
        ]);

        $response->assertStatus(422);
    }


    public function test_registered_user_can_create_and_interact_in_public_rooms() {
        $user = User::factory()->create(['is_guest' => false]);

        $roomResponse = $this->actingAs($user)->postJson('/api/rooms', [
            'description' => 'Nova Sala Pública',
            'is_public' => true,
        ]);
        $roomResponse->assertStatus(201);

        $uuid = $roomResponse->json('data.uuid');

        $this->actingAs($user)->postJson("/api/rooms/{$uuid}/ideas", [
            'content' => 'Ideia oficial em sala pública',
        ])->assertStatus(201);
    }


    public function test_first_interaction_by_guest_persists_one_guest_user_and_room_user_relationship() {
        $room = Room::factory()->create(['is_public' => false]);

        $this->assertDatabaseCount('users', 1);

        $this->postJson("/api/rooms/{$room->uuid}/ideas", [
            'content' => 'Primeira interação de um guest',
        ])->assertStatus(201);

        $this->assertDatabaseCount('users', 2); // 1 guest + 1 system user
        $this->assertDatabaseCount('room_users', 1);
        $this->assertDatabaseHas('users', ['is_guest' => true]);
    }

    public function test_guest_retains_same_author_assigned_during_get_request_on_subsequent_post() {
        $room = Room::factory()->create(['is_public' => false]);
        $idea = Idea::factory()->for($room)->create();

        // 1. O usuário acessa a sala via GET (primeiro contato)
        $getResponse = $this->getJson("/api/rooms/{$room->uuid}");

        $getResponse->assertStatus(200);

        // Captura o cookie do guest gerado na leitura
        $guestCookie = $getResponse->getCookie('guest_user_id');
        $this->assertNotNull($guestCookie, 'O cookie do guest deveria ser emitido na requisição GET.');

        // 2. O mesmo usuário faz uma requisição POST (ex: comentando) enviando o cookie capturado
        $postResponse = $this->withCookie('guest_user_id', $guestCookie->getValue())
            ->postJson("/api/ideas/{$idea->id}/comments", [
                'content' => 'Comentário mantendo a identidade',
            ]);

        $postResponse->assertStatus(201);

        // 3. Validação: Apenas 1 usuário guest foi persistido no banco
        $this->assertDatabaseCount('users', 2); // 1 criador da sala + 1 guest

        // 4. Validação crucial: O vínculo na tabela pivô (room_users) deve existir 
        // e o author_id atribuído deve ser consistente com a sessão.
        $this->assertDatabaseCount('room_users', 1);

        $roomUser = \App\Models\RoomUser::first();
        $this->assertNotNull($roomUser->author_id);
    }
}
