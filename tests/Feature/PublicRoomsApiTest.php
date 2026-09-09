<?php

namespace Tests\Feature;

use App\Models\Room;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicRoomsApiTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Testa se o endpoint de salas públicas retorna o formato esperado e trata
     * corretamente o campo expires_at_human quando a sala nunca expira.
     */
    public function test_public_rooms_api_returns_null_expires_at_human_when_room_never_expires()
    {
        Room::factory()->public()->neverExpires()->create();

        $response = $this->getJson('/api/rooms/public');

        $response->assertOk()
            ->assertJsonPath('data.0.room.expires_at', null)
            ->assertJsonPath('data.0.room.expires_at_human', null);
    }

    /**
     * Testa se a View Home renderiza o texto de "Sem expiração" quando a sala não expira.
     */
    public function test_home_page_renders_no_expires_text_for_rooms_without_expiration()
    {
        Room::factory()->public()->neverExpires()->create();

        $responsePt = $this->withHeaders(['Accept-Language' => 'pt-BR'])->get('/');
        $responsePt->assertOk()
            ->assertSee(__('app.ideas.no_expires', [], 'pt_BR'));

        $responseEn = $this->withHeaders(['Accept-Language' => 'en'])->get('/');
        $responseEn->assertOk()
            ->assertSee(__('app.ideas.no_expires', [], 'en'));
    }
}