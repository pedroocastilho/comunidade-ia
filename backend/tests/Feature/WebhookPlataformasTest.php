<?php

namespace Tests\Feature;

use App\Models\Audio;
use App\Models\Compra;
use App\Models\User;
use App\Models\WebhookPagamento;
use App\Notifications\BoasVindasAssinante;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

/**
 * Adapters Kiwify e Hotmart do webhook de pagamento, baseados em payloads
 * reais de sandbox das plataformas (versoes reduzidas).
 */
class WebhookPlataformasTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config([
            'services.kiwify.webhook_token' => 'kiwify-secreto',
            'services.hotmart.hottok' => 'hottok-secreto',
            'services.webhook_pagamento.token' => 'token-generico',
        ]);
    }

    // ------------------------------------------------------------ Kiwify

    private function payloadKiwify(string $evento, array $extra = []): array
    {
        return array_merge([
            'order_id' => 'fca60637-5e2f-4563-bd92-64a9fab5dcb8',
            'order_status' => 'paid',
            'product_type' => 'membership',
            'webhook_event_type' => $evento,
            'Product' => ['product_id' => 'prod-123', 'product_name' => 'Circulo Aura'],
            'Customer' => ['full_name' => 'Maria Silva', 'first_name' => 'Maria', 'email' => 'maria@exemplo.com'],
            'Subscription' => ['id' => 'sub-1', 'status' => 'active'],
        ], $extra);
    }

    private function postKiwify(array $payload)
    {
        $corpo = json_encode($payload);
        $assinatura = hash_hmac('sha1', $corpo, 'kiwify-secreto');

        return $this->call('POST', "/api/webhooks/pagamento/kiwify?signature={$assinatura}", [], [], [],
            ['CONTENT_TYPE' => 'application/json'], $corpo);
    }

    public function test_kiwify_assinatura_negada_sem_assinatura_valida(): void
    {
        $this->postJson('/api/webhooks/pagamento/kiwify', $this->payloadKiwify('order_approved'))
            ->assertStatus(401);

        $corpo = json_encode($this->payloadKiwify('order_approved'));
        $this->call('POST', '/api/webhooks/pagamento/kiwify?signature=errada', [], [], [],
            ['CONTENT_TYPE' => 'application/json'], $corpo)->assertStatus(401);
    }

    public function test_kiwify_compra_aprovada_cria_conta_e_envia_boas_vindas(): void
    {
        Notification::fake();

        $this->postKiwify($this->payloadKiwify('order_approved'))->assertOk();

        $user = User::where('email', 'maria@exemplo.com')->first();
        $this->assertNotNull($user);
        $this->assertTrue($user->tem_acesso);
        $this->assertSame('ativa', $user->assinatura_status);
        $this->assertSame('Maria Silva', $user->name);
        Notification::assertSentTo($user, BoasVindasAssinante::class);

        $this->assertTrue(WebhookPagamento::where('plataforma', 'kiwify')
            ->where('evento', 'assinatura_ativa')->where('processado', true)->exists());
    }

    public function test_kiwify_renovacao_nao_reenvia_boas_vindas(): void
    {
        Notification::fake();
        User::factory()->create(['email' => 'maria@exemplo.com', 'tem_acesso' => false]);

        $this->postKiwify($this->payloadKiwify('subscription_renewed'))->assertOk();

        $this->assertTrue(User::where('email', 'maria@exemplo.com')->value('tem_acesso'));
        Notification::assertNothingSent();
    }

    public function test_kiwify_cancelamento_reembolso_e_chargeback_bloqueiam(): void
    {
        foreach (['subscription_canceled', 'order_refunded', 'chargeback'] as $evento) {
            $user = User::factory()->create(['tem_acesso' => true, 'assinatura_status' => 'ativa']);
            $payload = $this->payloadKiwify($evento, ['Customer' => ['full_name' => 'X', 'email' => $user->email]]);

            $this->postKiwify($payload)->assertOk();

            $user->refresh();
            $this->assertFalse($user->tem_acesso, "Evento {$evento} nao bloqueou");
            $this->assertSame('cancelada', $user->assinatura_status);
        }
    }

    public function test_kiwify_atraso_mantem_acesso(): void
    {
        $user = User::factory()->create(['tem_acesso' => true, 'assinatura_status' => 'ativa', 'email' => 'maria@exemplo.com']);

        $this->postKiwify($this->payloadKiwify('subscription_late'))->assertOk();

        $user->refresh();
        $this->assertTrue($user->tem_acesso);
        $this->assertSame('atrasada', $user->assinatura_status);
    }

    public function test_kiwify_reembolso_de_compra_avulsa_revoga_conteudo(): void
    {
        $user = User::factory()->create(['email' => 'maria@exemplo.com']);
        $audio = Audio::factory()->create(['premium' => true, 'produto_externo_id' => 'prod-123']);
        Compra::create(['user_id' => $user->id, 'audio_id' => $audio->id, 'produto_externo_id' => 'prod-123']);

        $payload = $this->payloadKiwify('order_refunded', ['product_type' => 'course']);
        unset($payload['Subscription']);

        $this->postKiwify($payload)->assertOk();

        $this->assertSame(0, Compra::count());
    }

    // ------------------------------------------------------------ Hotmart

    private function payloadHotmart(string $evento, array $extra = []): array
    {
        return array_merge_recursive([
            'event' => $evento,
            'version' => '2.0.0',
            'data' => [
                'product' => ['id' => 788921, 'name' => 'Circulo Aura'],
                'buyer' => ['name' => 'Joao Souza', 'email' => 'joao@exemplo.com'],
                'purchase' => ['transaction' => 'HP1', 'status' => 'APPROVED'],
                'subscription' => ['status' => 'ACTIVE', 'plan' => ['name' => 'Mensal']],
            ],
        ], $extra);
    }

    private function postHotmart(array $payload)
    {
        return $this->postJson('/api/webhooks/pagamento/hotmart', $payload, ['X-HOTMART-HOTTOK' => 'hottok-secreto']);
    }

    public function test_hotmart_negado_sem_hottok(): void
    {
        $this->postJson('/api/webhooks/pagamento/hotmart', $this->payloadHotmart('PURCHASE_APPROVED'))
            ->assertStatus(401);
        $this->postJson('/api/webhooks/pagamento/hotmart', $this->payloadHotmart('PURCHASE_APPROVED'),
            ['X-HOTMART-HOTTOK' => 'errado'])->assertStatus(401);
    }

    public function test_hotmart_compra_aprovada_cria_conta_e_envia_boas_vindas(): void
    {
        Notification::fake();

        $this->postHotmart($this->payloadHotmart('PURCHASE_APPROVED'))->assertOk();

        $user = User::where('email', 'joao@exemplo.com')->first();
        $this->assertNotNull($user);
        $this->assertTrue($user->tem_acesso);
        Notification::assertSentTo($user, BoasVindasAssinante::class);
    }

    public function test_hotmart_reembolso_e_chargeback_bloqueiam_cancelamento_de_assinatura_tambem(): void
    {
        foreach (['PURCHASE_REFUNDED', 'PURCHASE_CHARGEBACK', 'PURCHASE_CANCELED'] as $evento) {
            $user = User::factory()->create(['tem_acesso' => true, 'assinatura_status' => 'ativa']);
            $payload = $this->payloadHotmart($evento);
            $payload['data']['buyer']['email'] = $user->email;

            $this->postHotmart($payload)->assertOk();

            $this->assertFalse($user->refresh()->tem_acesso, "Evento {$evento} nao bloqueou");
        }
    }

    public function test_hotmart_cancelamento_de_assinatura_usa_email_do_subscriber(): void
    {
        $user = User::factory()->create(['tem_acesso' => true]);

        $this->postHotmart([
            'event' => 'SUBSCRIPTION_CANCELLATION',
            'data' => [
                'product' => ['id' => 788921],
                'subscriber' => ['name' => 'X', 'email' => $user->email],
                'subscription' => ['id' => 1],
            ],
        ])->assertOk();

        $this->assertFalse($user->refresh()->tem_acesso);
    }

    public function test_hotmart_atraso_e_disputa_mantem_acesso(): void
    {
        foreach (['PURCHASE_DELAYED', 'PURCHASE_PROTEST'] as $evento) {
            $user = User::factory()->create(['tem_acesso' => true, 'assinatura_status' => 'ativa']);
            $payload = $this->payloadHotmart($evento);
            $payload['data']['buyer']['email'] = $user->email;

            $this->postHotmart($payload)->assertOk();

            $user->refresh();
            $this->assertTrue($user->tem_acesso, "Evento {$evento} bloqueou indevidamente");
            $this->assertSame('atrasada', $user->assinatura_status);
        }
    }

    public function test_hotmart_eventos_sem_efeito_sao_ignorados_sem_erro(): void
    {
        foreach (['PURCHASE_BILLET_PRINTED', 'PURCHASE_EXPIRED', 'UPDATE_SUBSCRIPTION_CHARGE_DATE', 'SWITCH_PLAN'] as $evento) {
            $this->postHotmart($this->payloadHotmart($evento))->assertOk();
        }

        $this->assertSame(0, User::count());
        $this->assertSame(4, WebhookPagamento::where('evento', 'ignorar')->where('processado', true)->count());
        $this->assertSame(0, WebhookPagamento::whereNotNull('erro')->count());
    }

    public function test_token_generico_continua_valendo_para_plataforma_generica(): void
    {
        $this->postJson('/api/webhooks/pagamento/generico', [
            'evento' => 'assinatura_ativa', 'email' => 'x@exemplo.com',
        ], ['X-Webhook-Token' => 'token-generico'])->assertOk();

        $this->assertTrue(User::where('email', 'x@exemplo.com')->value('tem_acesso'));
    }
}
