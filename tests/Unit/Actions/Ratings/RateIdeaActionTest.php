<?php

namespace Tests\Unit\Actions\Ratings;

use App\Actions\Ratings\RateIdeaAction;
use App\Models\Author;
use App\Models\Idea;
use App\Models\Rating;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class RateIdeaActionTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_creates_rating_and_updates_idea_aggregates(): void
    {
        // 1. Arrange
        $user = User::factory()->create();
        $author = Author::factory()->create(['type' => 0]);
        $idea = Idea::factory()->create([
            'total_score' => 0,
            'ratings_count' => 0,
            'avg_score' => 0.00,
        ]);

        // 2. Act
        $action = app(RateIdeaAction::class);
        $rating = $action->execute($idea, $user, 5, 'Excelente ideia!');

        // 3. Assert
        $this->assertEquals(5, $rating->score);
        $this->assertEquals('Excelente ideia!', $rating->feedback);

        // Verifica se a ideia teve os dados recarregados/recalculados corretamente
        $this->assertDatabaseHas('ideas', [
            'id' => $idea->id,
            'total_score' => 5,
            'ratings_count' => 1,
            'avg_score' => 5.00,
        ]);
    }

    public function test_it_throws_validation_exception_if_user_already_rated_the_idea(): void
    {
        // 1. Arrange
        $user = User::factory()->create();
        $author = Author::factory()->create(['type' => 0]);
        $idea = Idea::factory()->create();

        Rating::factory()->create([
            'idea_id' => $idea->id,
            'user_id' => $user->id,
            'author_id' => $author->id,
        ]);

        $this->expectException(ValidationException::class);

        // 2. Act
        $action = app(RateIdeaAction::class);
        $action->execute($idea, $user, 4, 'Tentando avaliar novamente');
    }
}