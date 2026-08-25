<?php

namespace Tests\Feature;

use App\Jobs\ExtrairMemorias;
use App\Models\AuraConversa;
use App\Models\AuraMensagem;
use App\Models\User;
use App\Services\ClassificadorConversa;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class AuraMemoriaJobTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['services.anthropic.key' => 'chave-teste']);
    }

    private function conversaComMensagens(array $textosUsuario): AuraConversa
    {
        $conversa = AuraConversa::factory()->for(User::factory()->create())->create();
        foreach ($textosUsuario as $texto) {
            AuraMensagem::factory()->for($conversa, 'conversa')->create(['papel' => 'user', 'conteudo' => $texto]);
        }

        return $conversa;
    }

    public function test_job_extrai_memorias_sem_duplicar(): void
    {
        Http::fake([
            'api.anthropic.com/*' => Http::response([
                'content' => [['type' => 'text', 'text' => '[{"tipo":"fato","conteudo":"Tem dois filhos pequenos."},{"tipo":"objetivo","conteudo":"Quer abrir uma confeitaria."}]']],
                'usage' => [],
            ]),
        ]);

        $conversa = $this->conversaComMensagens(['tenho dois filhos e sonho em abrir uma confeitaria']);

        (new ExtrairMemorias($conversa))->handle(app(\App\Services\AnthropicService::class));
        (new ExtrairMemorias($conversa))->handle(app(\App\Services\AnthropicService::class));

        $memorias = $conversa->user->auraMemorias;
        $this->assertCount(2, $memorias);
        $this->assertSame('conversa', $memorias->first()->origem);
    }

    public function test_job_ignora_resposta_invalida_e_erro_de_api(): void
    {
        Http::fake(['api.anthropic.com/*' => Http::response(['content' => [['type' => 'text', 'text' => 'nao sei']], 'usage' => []])]);
        $conversa = $this->conversaComMensagens(['oi']);
        (new ExtrairMemorias($conversa))->handle(app(\App\Services\AnthropicService::class));
        $this->assertCount(0, $conversa->user->auraMemorias);

        Http::fake(['api.anthropic.com/*' => Http::response([], 500)]);
        (new ExtrairMemorias($conversa))->handle(app(\App\Services\AnthropicService::class));
        $this->assertCount(0, $conversa->user->auraMemorias()->get());
    }

    public function test_classificador_por_palavras_chave(): void
    {
        $classificador = new ClassificadorConversa;

        $prosperidade = $this->conversaComMensagens(['quero sair das dividas e ganhar mais dinheiro']);
        $classificador->classificar($prosperidade);
        $this->assertSame('prosperidade', $prosperidade->fresh()->classificacao);

        $plataforma = $this->conversaComMensagens(['nao consigo fazer login na plataforma']);
        $classificador->classificar($plataforma);
        $this->assertSame('duvida-plataforma', $plataforma->fresh()->classificacao);

        $crise = $this->conversaComMensagens(['sobre dinheiro']);
        $crise->update(['classificacao' => 'crise']);
        $classificador->classificar($crise);
        $this->assertSame('crise', $crise->fresh()->classificacao);
    }
}
