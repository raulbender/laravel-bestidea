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

    /**
     * Testa se a View Home renderiza as chaves de tradução conforme o idioma solicitado.
     */
    public function test_home_page_renders_translated_strings_based_on_header() {
        // Acessa a Home em PT-BR
        $responsePt = $this->withHeaders(['Accept-Language' => 'pt-BR'])->get('/');
        $responsePt->assertOk()
            ->assertSee(__('app.home.hero.title', [], 'pt_BR'));

        // Acessa a Home em EN
        $responseEn = $this->withHeaders(['Accept-Language' => 'en'])->get('/');
        $responseEn->assertOk()
            ->assertSee(__('app.home.hero.title', [], 'en'));
    }

    /**
     * Testa se a API de salas públicas traduz o expires_at_human com base no Accept-Language.
     */
    public function test_public_rooms_api_translates_expires_at_human_based_on_header() {
        // Fixa o tempo do teste usando o helper do Laravel
        $this->travelTo(now()->startOfHour());

        // Cria uma sala pública que expira em exatos 2 dias
        Room::factory()->public()->expiresInDays(2)->create();

        // 1. Requisição em PT-BR
        $responsePt = $this->withHeaders(['Accept-Language' => 'pt-BR'])
            ->getJson('/api/rooms/public');

        // 2. Requisição em EN
        $responseEn = $this->withHeaders(['Accept-Language' => 'en'])
            ->getJson('/api/rooms/public');

        $responsePt->assertOk();
        $responseEn->assertOk();

        $expiresPt = $responsePt->json('data.0.expires_at_human');
        $expiresEn = $responseEn->json('data.0.expires_at_human');

        $this->assertNotNull($expiresPt);
        $this->assertNotNull($expiresEn);

        // Aceita singular ("dia") ou plural ("dias") em PT
        $this->assertMatchesRegularExpression('/dia(s)?/', strtolower($expiresPt));

        // Aceita singular ("day") ou plural ("days") em EN
        $this->assertMatchesRegularExpression('/day(s)?/', strtolower($expiresEn));
    }
}
