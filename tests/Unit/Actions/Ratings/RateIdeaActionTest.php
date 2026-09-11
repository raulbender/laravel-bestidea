<?php

namespace Tests\Unit\Actions\Ratings;

use App\Actions\Ratings\RateIdeaAction;
use App\Models\Author;
use App\Models\Idea;
use App\Models\Rating;
use App\Models\User;
use App\Models\Room;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class RateIdeaActionTest extends TestCase {
    use RefreshDatabase;

    public function test_it_creates_rating_and_updates_idea_aggregates(): void {
        // 1. Arrange
        $user = User::factory()->create();
        $idea = Idea::factory()->create([
            'total_score' => 0,
            'ratings_count' => 0,
            'avg_score' => 0.00,
        ]);

        // 2. Act
        $action = app(RateIdeaAction::class);
        $rating = $action->execute($idea, $user, 5);

        // 3. Assert
        $this->assertEquals(5, $rating->score);

        // Verifica se a ideia teve os dados recarregados/recalculados corretamente
        $this->assertDatabaseHas('ideas', [
            'id' => $idea->id,
            'total_score' => 5,
            'ratings_count' => 1,
            'avg_score' => 5.00,
        ]);
    }

    public function test_it_creates_rating_and_updates_idea_aggregates_second_version(): void 
{
    // 1. Arrange
    $user = User::factory()->create();
    
    // Importante: Cria a ideia já vinculada a uma Sala existente
    $idea = Idea::factory()->for(Room::factory())->create([
        'total_score'   => 0,
        'ratings_count' => 0,
        'avg_score'     => 0.00,
    ]);

    // 2. Act
    $action = app(RateIdeaAction::class);
    $rating = $action->execute($idea, $user, 5);

    // 3. Assert
    // Valida o retorno do Rating
    $this->assertInstanceOf(Rating::class, $rating);
    $this->assertEquals(5, $rating->score);
    $this->assertDatabaseHas('ratings', [
        'idea_id' => $idea->id,
        'user_id' => $user->id,
        'score'   => 5,
    ]);

    // Recarrega o modelo da ideia direto do banco de dados
    $idea->refresh();

    // Valida os agregados recalculados
    $this->assertEquals(1, $idea->ratings_count);
    $this->assertEquals(5, $idea->total_score);
    $this->assertEquals(5.00, $idea->avg_score);
}


    public function test_it_updates_rating_score_and_recalculates_aggregates_if_user_rates_again(): void {
        // 1. Arrange
        $user = User::factory()->create();
        $author = Author::factory()->create(['type' => 0]);
        $idea = Idea::factory()->create([
            'ratings_count' => 1,
            'total_score'   => 2,
            'avg_score'     => 2.00,
        ]);

        // Rating inicial com nota 2
        $existingRating = Rating::factory()->create([
            'idea_id'   => $idea->id,
            'user_id'   => $user->id,
            'author_id' => $author->id,
            'score'     => 2,
        ]);

        // 2. Act (Atualiza a nota para 5)
        $action = app(RateIdeaAction::class);
        $updatedRating = $action->execute($idea, $user, 5);

        // 3. Assert
        // Garante que o mesmo registro foi alterado e que não foi criada uma nova linha no banco
        $this->assertEquals($existingRating->id, $updatedRating->id);
        $this->assertEquals(5, $updatedRating->score);
        $this->assertDatabaseCount('ratings', 1);

        // Valida se os agregados na tabela de ideias foram recalculados corretamente
        $idea->refresh();
        $this->assertEquals(1, $idea->ratings_count);
        $this->assertEquals(5, $idea->total_score);
        $this->assertEquals(5.00, $idea->avg_score);
    }

    public function test_it_removes_rating_and_recalculates_aggregates(): void
{
    // 1. Arrange
    $user = User::factory()->create();
    $author = Author::factory()->create(['type' => 0]);
    $idea = Idea::factory()->create([
        'ratings_count' => 1,
        'total_score'   => 4,
        'avg_score'     => 4.00,
    ]);

    Rating::factory()->create([
        'idea_id'   => $idea->id,
        'user_id'   => $user->id,
        'author_id' => $author->id,
        'score'     => 4,
    ]);

    // 2. Act
    $action = app(RateIdeaAction::class);
    $action->remove($idea, $user);

    // 3. Assert
    $this->assertDatabaseCount('ratings', 0);

    $idea->refresh();
    $this->assertEquals(0, $idea->ratings_count);
    $this->assertEquals(0, $idea->total_score);
    $this->assertEquals(0.00, $idea->avg_score);
}
}
