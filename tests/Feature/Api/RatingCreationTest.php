<?php

namespace Tests\Feature\Api;

use App\Models\Idea;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RatingCreationTest extends TestCase {
    use RefreshDatabase;

    /**
     * Test that a visitor can rate an idea with stars (1-5) and avg_score updates automatically.
     */
    public function test_user_can_rate_an_idea_and_recalculate_score(): void {
        $this->seed(\Database\Seeders\AuthorSeeder::class);

        $idea = Idea::factory()->create([
            'total_score'   => 0,
            'ratings_count' => 0,
            'avg_score'     => 0.00
        ]);

        $payload = [
            'score'    => 5, // Nota de 1 a 5
            'feedback' => 'Ideia fantástica!',
        ];

        $response = $this->postJson("/api/ideas/{$idea->id}/ratings", $payload);

        $response->assertStatus(201)
            ->assertJsonPath('data.total_score', 5)
            ->assertJsonPath('data.ratings_count', 1)
            ->assertJsonPath('data.avg_score', 5.00);

        // Garante a gravação na tabela ratings
        $this->assertDatabaseHas('ratings', [
            'idea_id' => $idea->id,
            'score'   => 5,
        ]);

        // Garante a atualização na tabela ideas
        $this->assertDatabaseHas('ideas', [
            'id'            => $idea->id,
            'total_score'   => 5,
            'ratings_count' => 1,
            'avg_score'     => 5.00,
        ]);
    }

    /**
     * Test validation rules for rating values (must be integer between 1 and 5).
     */
    public function test_rating_score_must_be_an_integer_between_1_and_5(): void {
        $idea = Idea::factory()->create();

        // 1. Teste de valor acima do limite (6)
        $this->postJson("/api/ideas/{$idea->id}/ratings", ['score' => 6])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['score']);

        // 2. Teste de valor abaixo do limite (0)
        $this->postJson("/api/ideas/{$idea->id}/ratings", ['score' => 0])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['score']);

        // 3. Teste de número não inteiro (3.5)
        $this->postJson("/api/ideas/{$idea->id}/ratings", ['score' => 3.5])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['score']);
    }

    /**
     * Test that a user cannot rate the same idea twice in the same room.
     */
    public function test_user_cannot_rate_same_idea_multiple_times(): void {
        $this->seed(\Database\Seeders\AuthorSeeder::class);

        $idea = Idea::factory()->create();

        // Primeira avaliação
        $this->postJson("/api/ideas/{$idea->id}/ratings", ['score' => 4])
            ->assertStatus(201);

        // Segunda avaliação (deve falhar por conta da restrição de unicidade)
        $this->postJson("/api/ideas/{$idea->id}/ratings", ['score' => 5])
            ->assertStatus(422);
    }

    /**
     * Test that a user can rate multiple different ideas in the same room.
     */
    public function test_user_can_rate_multiple_different_ideas_in_same_room(): void {
        $this->seed(\Database\Seeders\AuthorSeeder::class);

        // Criamos duas ideias vinculadas à mesma sala
        $ideaA = Idea::factory()->create();
        $ideaB = Idea::factory()->create(['room_id' => $ideaA->room_id]);

        // Avalia a Ideia A
        $this->postJson("/api/ideas/{$ideaA->id}/ratings", ['score' => 4])
            ->assertStatus(201);

        // Avalia a Ideia B (deve ser permitido com sucesso)
        $this->postJson("/api/ideas/{$ideaB->id}/ratings", ['score' => 5])
            ->assertStatus(201);

        // Garante que ambos os registros foram persistidos no banco
        $this->assertDatabaseCount('ratings', 2);
    }

    /**
     * Test that multiple users rating the same idea updates the average score correctly.
     */
    public function test_multiple_users_rating_same_idea_updates_avg_score_correctly(): void {
        $this->seed(\Database\Seeders\AuthorSeeder::class);

        $idea = Idea::factory()->create([
            'total_score'   => 0,
            'ratings_count' => 0,
            'avg_score'     => 0.00,
        ]);

        // Primeiro Usuário avalia com Nota 5
        $this->postJson("/api/ideas/{$idea->id}/ratings", ['score' => 5])
            ->assertStatus(201);

        // Limpa a autenticação em memória para simular uma nova requisição de outro visitante anônimo
        $this->flushHeaders();
        \Illuminate\Support\Facades\Auth::forgetGuards();

        // Segundo Usuário avalia com Nota 3
        $this->postJson("/api/ideas/{$idea->id}/ratings", ['score' => 2])
            ->assertStatus(201);

        // Média esperada: (5 + 3) / 2 = 4.00
        $this->assertDatabaseHas('ideas', [
            'id'            => $idea->id,
            'total_score'   => 7,
            'ratings_count' => 2,
            'avg_score'     => 3.50,
        ]);
    }
}
