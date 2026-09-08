<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Room;

class SetLocaleMiddlewareTest extends TestCase {
    use RefreshDatabase;
    public function test_it_sets_locale_to_pt_br_when_browser_requests_pt_br() {
        // Envia requisição com o cabeçalho Accept-Language solicitando pt-BR
        $response = $this->withHeaders([
            'Accept-Language' => 'pt-BR,pt;q=0.9',
        ])->get('/');

        $response->assertStatus(200);
        // Verifica se a aplicação definiu o locale global como pt_BR
        $this->assertEquals('pt_BR', app()->getLocale());
    }

    public function test_it_sets_locale_to_en_when_browser_requests_en() {
        // Envia requisição solicitando inglês
        $response = $this->withHeaders([
            'Accept-Language' => 'en-US,en;q=0.9',
        ])->get('/');

        $response->assertStatus(200);
        // Verifica se a aplicação definiu o locale global como en
        $this->assertEquals('en', app()->getLocale());
    }

    public function test_translates_persona_author_name_based_on_header() {
        $this->seed(\Database\Seeders\AuthorSeeder::class);

        $room = Room::factory()->create();

        // 1. Requisita em Português
        $responsePt = $this->withHeaders([
            'Accept-Language' => 'pt-BR',
        ])->getJson("/api/rooms/{$room->uuid}");

        $responsePt->assertOk();

        // 2. Requisita a mesma sala em Inglês
        $responseEn = $this->withHeaders([
            'Accept-Language' => 'en',
        ])->getJson("/api/rooms/{$room->uuid}");

        $responseEn->assertOk();

        // Pega os nomes retornados nas duas requisições para a mesma sala
        $nameInPt = $responsePt->json('data.my_persona.name');
        $nameInEn = $responseEn->json('data.my_persona.name');

        // Valida se ambos não vieram nulos
        $this->assertNotNull($nameInPt);
        $this->assertNotNull($nameInEn);

        // Garante que o idioma alterou o valor exibido (ex: 'Raposa' != 'Fox')
        $this->assertNotEquals($nameInPt, $nameInEn);
    }
}
