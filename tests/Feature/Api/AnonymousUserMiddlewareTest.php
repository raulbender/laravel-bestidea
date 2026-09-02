<?php

namespace Tests\Feature\Api;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AnonymousUserMiddlewareTest extends TestCase {
    use RefreshDatabase;

    public function test_unauthenticated_request_creates_and_authenticates_an_anonymous_user(): void {
        // 1. Arrange: Database starts empty
        $this->assertDatabaseCount('users', 0);

        // 2. Act: Make a request to any public API route
        $response = $this->getJson('/api/rooms/public');

        // 3. Assert: A guest user was created in DB and authenticated
        $response->assertStatus(200);

        $this->assertDatabaseCount('users', 1);

        $guest = User::first();
        $this->assertTrue($guest->is_anonymous);
        $this->assertStringStartsWith('Guest', $guest->name);
        $this->assertMatchesRegularExpression('/^Guest #[a-zA-Z0-9]+$/', $guest->name);
        $this->assertNull($guest->email);
        $this->assertNotNull($guest->id);
    }

    public function test_subsequent_request_reuses_the_existing_anonymous_user_session(): void {
        // 1. First request creates the guest user
        $this->getJson('/api/rooms/public');
        $this->assertDatabaseCount('users', 1);

        // 2. Second request should reuse the session/cookie without creating a 2nd user
        $this->getJson('/api/rooms/public');
        $this->assertDatabaseCount('users', 1);
    }
}
