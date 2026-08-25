<?php

namespace Tests\Unit;

use App\Models\IaConfiguracao;
use App\Services\AnthropicException;
use App\Services\AnthropicService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class AnthropicServiceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['services.anthropic.key' => 'chave-teste']);
        IaConfiguracao::create(['chave' => 'modelo', 'valor' => 'claude-haiku-4-5-20251001']);
        IaConfiguracao::create(['chave' => 'temperatura', 'valor' => '0.8']);
        IaConfiguracao::create(['chave' => 'max_tokens', 'valor' => '512']);
    }

    public function test_monta_payload_com_config_do_banco(): void
    {
        Http::fake([
            'api.anthropic.com/*' => Http::response([
                'content' => [['type' => 'text', 'text' => 'Oi!']],
                'usage' => ['input_tokens' => 10, 'output_tokens' => 5],
                'stop_reason' => 'end_turn',
            ]),
        ]);

        $resposta = (new AnthropicService)->mensagens([
            'system' => 'Voce e a Aura.',
            'messages' => [['role' => 'user', 'content' => 'ola']],
        ]);

        $this->assertSame('Oi!', $resposta['content'][0]['text']);

        Http::assertSent(function ($request) {
            return $request->hasHeader('x-api-key', 'chave-teste')
                && $request->hasHeader('anthropic-version', '2023-06-01')
                && $request['model'] === 'claude-haiku-4-5-20251001'
                && $request['max_tokens'] === 512
                && $request['temperature'] === 0.8
                && $request['system'] === 'Voce e a Aura.';
        });
    }

    public function test_envia_tools_quando_fornecidas(): void
    {
        Http::fake(['api.anthropic.com/*' => Http::response(['content' => [], 'usage' => []])]);

        (new AnthropicService)->mensagens([
            'system' => 's',
            'messages' => [['role' => 'user', 'content' => 'x']],
            'tools' => [['name' => 'buscar_conteudo', 'description' => 'd', 'input_schema' => ['type' => 'object']]],
        ]);

        Http::assertSent(fn ($request) => ($request['tools'][0]['name'] ?? null) === 'buscar_conteudo');
    }

    public function test_erro_http_lanca_excecao(): void
    {
        Http::fake(['api.anthropic.com/*' => Http::response(['error' => ['message' => 'overloaded']], 529)]);

        $this->expectException(AnthropicException::class);

        (new AnthropicService)->mensagens([
            'system' => 's',
            'messages' => [['role' => 'user', 'content' => 'x']],
        ]);
    }
}
