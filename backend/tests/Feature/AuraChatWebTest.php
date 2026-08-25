<?php

namespace Tests\Feature;

use App\Models\AuraConversa;
use App\Models\EventoAnalytics;
use App\Models\IaConfiguracao;
use App\Models\User;
use Database\Seeders\IaConfiguracaoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class AuraChatWebTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['services.anthropic.key' => 'chave-teste']);
        $this->seed(IaConfiguracaoSeeder::class);

        Http::fake([
            'api.anthropic.com/*' => Http::response([
                'content' => [['type' => 'text', 'text' => 'Oi! Como posso te acompanhar hoje?']],
                'stop_reason' => 'end_turn',
                'usage' => ['input_tokens' => 100, 'output_tokens' => 20],
            ]),
        ]);
    }

    private function aluno(): User
    {
        return User::factory()->create(['tem_acesso' => true, 'onboarding_completo_em' => now()]);
    }

    public function test_pagina_do_chat_renderiza_com_conversas(): void
    {
        $user = $this->aluno();
        AuraConversa::factory()->for($user)->create(['titulo' => 'Primeira conversa']);

        $this->actingAs($user)->get('/aura')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('App/Aura')
                ->has('conversas', 1));
    }

    public function test_parametro_conversa_abre_a_conversa_escolhida(): void
    {
        $user = $this->aluno();
        AuraConversa::factory()->for($user)->create(['titulo' => 'Mais recente']);
        $antiga = AuraConversa::factory()->for($user)->create(['titulo' => 'Antiga', 'updated_at' => now()->subDay()]);

        $this->actingAs($user)->get('/aura?conversa='.$antiga->id)
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page->where('conversa_ativa', $antiga->id));

        // conversa de outro usuario nao abre: cai na mais recente do proprio usuario
        $alheia = AuraConversa::factory()->create();
        $this->actingAs($user)->get('/aura?conversa='.$alheia->id)
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page->whereNot('conversa_ativa', $alheia->id));
    }

    public function test_mensagem_cria_conversa_e_retorna_resposta(): void
    {
        $user = $this->aluno();

        $resposta = $this->actingAs($user)->postJson('/aura/mensagem', ['texto' => 'ola Aura']);

        $resposta->assertOk()
            ->assertJsonPath('mensagem.papel', 'assistant')
            ->assertJsonStructure(['mensagem' => ['conteudo'], 'conversa_id']);

        $this->assertSame(1, $user->auraConversas()->count());
        $this->assertSame(2, $user->auraConversas->first()->mensagens()->count());
    }

    public function test_mensagem_continua_conversa_existente(): void
    {
        $user = $this->aluno();
        $conversa = AuraConversa::factory()->for($user)->create();

        $this->actingAs($user)->postJson('/aura/mensagem', ['texto' => 'oi', 'conversa_id' => $conversa->id])
            ->assertOk()
            ->assertJsonPath('conversa_id', $conversa->id);

        $this->assertSame(1, $user->auraConversas()->count());
    }

    public function test_nao_acessa_conversa_de_outro_usuario(): void
    {
        $user = $this->aluno();
        $conversaAlheia = AuraConversa::factory()->create();

        $this->actingAs($user)
            ->postJson('/aura/mensagem', ['texto' => 'oi', 'conversa_id' => $conversaAlheia->id])
            ->assertNotFound();
    }

    public function test_evento_aura_chat_started_uma_vez_por_dia(): void
    {
        $user = $this->aluno();

        $this->actingAs($user)->postJson('/aura/mensagem', ['texto' => 'oi']);
        $this->actingAs($user)->postJson('/aura/mensagem', ['texto' => 'tudo bem?']);

        $this->assertSame(1, EventoAnalytics::where('nome', 'aura_chat_started')
            ->where('user_id', $user->id)->count());
    }

    public function test_teto_diario_de_tokens_bloqueia_com_429(): void
    {
        IaConfiguracao::where('chave', 'teto_diario_tokens')->update(['valor' => '100']);
        $user = $this->aluno();

        $this->actingAs($user)->postJson('/aura/mensagem', ['texto' => 'primeira'])->assertOk(); // consome 120

        $this->actingAs($user)->postJson('/aura/mensagem', ['texto' => 'segunda'])->assertStatus(429);
    }

    public function test_exige_texto(): void
    {
        $this->actingAs($this->aluno())->postJson('/aura/mensagem', ['texto' => ''])->assertStatus(422);
    }
}
