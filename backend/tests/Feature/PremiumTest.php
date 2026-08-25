<?php

namespace Tests\Feature;

use App\Models\Audio;
use App\Models\Aula;
use App\Models\Compra;
use App\Models\Curso;
use App\Models\EventoAnalytics;
use App\Models\Modulo;
use App\Models\User;
use App\Models\WebhookPagamento;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class PremiumTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['services.webhook_pagamento.token' => 'segredo-teste']);
    }

    private function aluno(): User
    {
        return User::factory()->create(['tem_acesso' => true, 'onboarding_completo_em' => now()]);
    }

    private function cursoPremium(): Curso
    {
        $curso = Curso::factory()->create([
            'status' => 'publicado',
            'slug' => 'mestre-da-manifestacao',
            'premium' => true,
            'produto_externo_id' => 'prod-777',
            'checkout_url' => 'https://pay.exemplo/prod-777',
        ]);
        $modulo = Modulo::factory()->for($curso)->create();
        Aula::factory()->for($modulo)->create();

        return $curso;
    }

    public function test_webhook_compra_aprovada_libera_e_registra_evento(): void
    {
        $user = $this->aluno();
        $this->cursoPremium();

        $this->postJson('/api/webhooks/pagamento/generico', [
            'evento' => 'compra_aprovada',
            'email' => $user->email,
            'produto_externo_id' => 'prod-777',
        ], ['X-Webhook-Token' => 'segredo-teste'])->assertOk();

        $this->assertTrue($user->fresh()->comprou('prod-777'));
        $this->assertNotNull(Compra::first()->curso_id);
        $this->assertSame(1, EventoAnalytics::where('nome', 'premium_purchased')->count());
        $this->assertTrue(WebhookPagamento::first()->processado);
    }

    public function test_compra_de_produto_inexistente_fica_com_erro(): void
    {
        $user = $this->aluno();

        $this->postJson('/api/webhooks/pagamento/generico', [
            'evento' => 'compra_aprovada',
            'email' => $user->email,
            'produto_externo_id' => 'nao-existe',
        ], ['X-Webhook-Token' => 'segredo-teste'])->assertOk();

        $this->assertFalse(WebhookPagamento::first()->processado);
        $this->assertNotNull(WebhookPagamento::first()->erro);
    }

    public function test_curso_premium_sem_compra_vira_pagina_de_venda(): void
    {
        $user = $this->aluno();
        $curso = $this->cursoPremium();

        $this->actingAs($user)->get('/cursos/mestre-da-manifestacao')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('premium_bloqueado', true)
                ->where('checkout_url', 'https://pay.exemplo/prod-777'));

        $this->assertSame(1, EventoAnalytics::where('nome', 'premium_viewed')->count());

        // aula do curso bloqueado redireciona para a pagina de venda
        $aula = $curso->modulos->first()->aulas->first();
        $this->actingAs($user)->get("/aulas/{$aula->id}")
            ->assertRedirect(route('curso', 'mestre-da-manifestacao'));
    }

    public function test_curso_premium_comprado_libera_tudo(): void
    {
        $user = $this->aluno();
        $curso = $this->cursoPremium();
        Compra::create(['user_id' => $user->id, 'produto_externo_id' => 'prod-777', 'curso_id' => $curso->id]);

        $this->actingAs($user)->get('/cursos/mestre-da-manifestacao')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page->where('premium_bloqueado', false));

        $aula = $curso->modulos->first()->aulas->first();
        $this->actingAs($user)->get("/aulas/{$aula->id}")->assertOk();
    }

    public function test_audio_premium_sem_compra_esconde_midia(): void
    {
        $user = $this->aluno();
        $audio = Audio::factory()->create([
            'status' => 'publicado',
            'premium' => true,
            'produto_externo_id' => 'aud-9',
            'arquivo_url' => 'https://cdn.exemplo/secreto.mp3',
            'checkout_url' => 'https://pay.exemplo/aud-9',
        ]);

        $this->actingAs($user)->get("/audios/{$audio->id}")
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('premium_bloqueado', true)
                ->where('audio.arquivo_url', null));

        Compra::create(['user_id' => $user->id, 'produto_externo_id' => 'aud-9', 'audio_id' => $audio->id]);

        $this->actingAs($user)->get("/audios/{$audio->id}")
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('premium_bloqueado', false)
                ->where('audio.arquivo_url', 'https://cdn.exemplo/secreto.mp3'));
    }

    public function test_vitrine_marca_conteudo_bloqueado(): void
    {
        $user = $this->aluno();
        $this->cursoPremium();

        $this->actingAs($user)->get('/cursos')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page->where('cursos.0.bloqueado', true));
    }
}
