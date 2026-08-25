<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\WebhookPagamento;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WebhookPagamentoTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['services.webhook_pagamento.token' => 'segredo-teste']);
    }

    private function enviar(array $payload, string $token = 'segredo-teste')
    {
        return $this->postJson('/api/webhooks/pagamento/generico', $payload, ['X-Webhook-Token' => $token]);
    }

    public function test_token_invalido_retorna_401(): void
    {
        $this->enviar(['evento' => 'assinatura_ativa', 'email' => 'a@b.com'], 'errado')->assertStatus(401);
        $this->assertSame(0, WebhookPagamento::count());
    }

    public function test_assinatura_ativa_cria_usuario_novo_com_acesso(): void
    {
        $this->enviar([
            'evento' => 'assinatura_ativa',
            'email' => 'nova@aura.com',
            'nome' => 'Nova Assinante',
        ])->assertOk();

        $user = User::where('email', 'nova@aura.com')->first();
        $this->assertNotNull($user);
        $this->assertTrue($user->tem_acesso);
        $this->assertSame('ativa', $user->assinatura_status);
        $this->assertSame('Nova Assinante', $user->name);

        $registro = WebhookPagamento::first();
        $this->assertTrue($registro->processado);
        $this->assertSame('generico', $registro->plataforma);
    }

    public function test_assinatura_ativa_reativa_usuario_existente(): void
    {
        $user = User::factory()->create(['email' => 'velho@aura.com', 'tem_acesso' => false, 'assinatura_status' => 'cancelada']);

        $this->enviar(['evento' => 'assinatura_ativa', 'email' => 'velho@aura.com'])->assertOk();

        $user = $user->fresh();
        $this->assertTrue($user->tem_acesso);
        $this->assertSame('ativa', $user->assinatura_status);
    }

    public function test_assinatura_cancelada_bloqueia_acesso(): void
    {
        $user = User::factory()->create(['email' => 'x@aura.com', 'tem_acesso' => true, 'assinatura_status' => 'ativa']);

        $this->enviar(['evento' => 'assinatura_cancelada', 'email' => 'x@aura.com'])->assertOk();

        $this->assertFalse($user->fresh()->tem_acesso);
        $this->assertSame('cancelada', $user->fresh()->assinatura_status);
    }

    public function test_pagamento_atrasado_mantem_acesso_em_carencia(): void
    {
        $user = User::factory()->create(['email' => 'y@aura.com', 'tem_acesso' => true, 'assinatura_status' => 'ativa']);

        $this->enviar(['evento' => 'pagamento_atrasado', 'email' => 'y@aura.com'])->assertOk();

        $this->assertTrue($user->fresh()->tem_acesso);
        $this->assertSame('atrasada', $user->fresh()->assinatura_status);
    }

    public function test_pagamento_regularizado_reativa(): void
    {
        $user = User::factory()->create(['email' => 'z@aura.com', 'tem_acesso' => true, 'assinatura_status' => 'atrasada']);

        $this->enviar(['evento' => 'pagamento_regularizado', 'email' => 'z@aura.com'])->assertOk();

        $this->assertSame('ativa', $user->fresh()->assinatura_status);
    }

    public function test_payload_desconhecido_e_salvo_com_erro_e_responde_200(): void
    {
        $this->enviar(['qualquer' => 'coisa'])->assertOk();

        $registro = WebhookPagamento::first();
        $this->assertFalse($registro->processado);
        $this->assertNotNull($registro->erro);
    }
}
