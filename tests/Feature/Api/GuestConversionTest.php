<?php

namespace Tests\Feature\Api;

use App\Models\Room;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class GuestConversionTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_user_can_convert_to_registered_user(): void
    {
        // 1. Create a guest user with an existing room
        $guestUser = User::factory()->create([
            'name'         => 'Guest #12345',
            'is_anonymous' => true,
            'email'        => null,
            'password'     => null,
        ]);

        $room = Room::factory()->create([
            'user_id'     => $guestUser->id,
            'description' => 'Guest Session Room',
        ]);

        // 2. Perform register conversion while logged in as guest
        $payload = [
            'name'                  => 'John Doe',
            'email'                 => 'john@example.com',
            'password'              => 'password123',
            'password_confirmation' => 'password123',
        ];

        $response = $this->actingAs($guestUser)
            ->postJson('/api/guest/register', $payload);

        // 3. Assert response structure and database state
        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Account converted successfully',
                'data'    => [
                    'id'           => $guestUser->id,
                    'name'         => 'John Doe',
                    'email'        => 'john@example.com',
                    'is_anonymous' => false,
                ],
            ]);

        $this->assertDatabaseHas('users', [
            'id'           => $guestUser->id,
            'name'         => 'John Doe',
            'email'        => 'john@example.com',
            'is_anonymous' => false,
        ]);

        // Ensure room ownership was preserved
        $this->assertDatabaseHas('rooms', [
            'id'      => $room->id,
            'user_id' => $guestUser->id,
        ]);
    }

    public function test_conversion_fails_if_email_already_taken(): void
    {
        User::factory()->create(['email' => 'existing@example.com']);

        $guestUser = User::factory()->create(['is_anonymous' => true]);

        $payload = [
            'name'                  => 'Jane Doe',
            'email'                 => 'existing@example.com',
            'password'              => 'password123',
            'password_confirmation' => 'password123',
        ];

        $response = $this->actingAs($guestUser)
            ->postJson('/api/guest/register', $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    public function test_already_registered_user_cannot_convert_again(): void
    {
        $registeredUser = User::factory()->create([
            'is_anonymous' => false,
            'email'        => 'permanent@example.com',
        ]);

        $payload = [
            'name'                  => 'New Name',
            'email'                 => 'new@example.com',
            'password'              => 'password123',
            'password_confirmation' => 'password123',
        ];

        $response = $this->actingAs($registeredUser)
            ->postJson('/api/guest/register', $payload);

        $response->assertStatus(403);
    }
}